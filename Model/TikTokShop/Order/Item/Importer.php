<?php

namespace M2E\TikTokShop\Model\TikTokShop\Order\Item;

class Importer extends \M2E\TikTokShop\Model\AbstractModel
{
    private \M2E\TikTokShop\Model\Order\Item $orderItem;
    private \Magento\Framework\Filesystem\DriverInterface $fileDriver;
    private \Magento\Framework\Filesystem $filesystem;
    private \Magento\Catalog\Model\Product\Media\Config $productMediaConfig;
    private \Magento\Directory\Model\CurrencyFactory $currencyFactory;
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;
    private \M2E\TikTokShop\Model\Config\Manager $configManager;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $configManager,
        \M2E\TikTokShop\Model\Connector\Client\Single $singleClient,
        \Magento\Framework\Filesystem\DriverPool $driverPool,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \M2E\TikTokShop\Model\Order\Item $orderItem
    ) {
        parent::__construct();
        $this->fileDriver = $driverPool->getDriver(\Magento\Framework\Filesystem\DriverPool::FILE);
        $this->filesystem = $filesystem;
        $this->productMediaConfig = $productMediaConfig;
        $this->currencyFactory = $currencyFactory;
        $this->orderItem = $orderItem;
        $this->singleClient = $singleClient;
        $this->configManager = $configManager;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function getDataFromChannel(): array
    {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Item\GetInfoCommand(
            $this->orderItem->getChannelProductId(),
            $this->orderItem->getAccount()->getServerHash(),
        );

        /** @var \M2E\TikTokShop\Model\Connector\Response $response */
        $response = $this->singleClient->process($command);

        return $response->getResponseData();
    }

    public function prepareDataForProductCreation(array $rawData): array
    {
        $preparedData = [];

        $preparedData['title'] = trim(strip_tags($rawData['title']));
        $preparedData['short_description'] = trim(\M2E\TikTokShop\Helper\Data::stripInvisibleTags($rawData['title']));

        $description = $rawData['description'] ?? $preparedData['title'];
        $preparedData['description'] = \M2E\TikTokShop\Helper\Data::stripInvisibleTags($description);

        if (!empty($rawData['sku'])) {
            $sku = $rawData['sku'];
        } else {
            $sku = \M2E\TikTokShop\Helper\Data::convertStringToSku($rawData['title']);
        }

        if (strlen($sku) > \M2E\TikTokShop\Helper\Magento\Product::SKU_MAX_LENGTH) {
            $hashLength = 10;
            $savedSkuLength = \M2E\TikTokShop\Helper\Magento\Product::SKU_MAX_LENGTH - $hashLength - 1;
            $hash = \M2E\TikTokShop\Helper\Data::generateUniqueHash($sku, $hashLength);

            $isSaveStart = (bool)$this->configManager->getGroupValue(
                '/order/magento/settings/',
                'save_start_of_long_sku_for_new_product'
            );

            if ($isSaveStart) {
                $sku = substr($sku, 0, $savedSkuLength) . '-' . $hash;
            } else {
                $sku = $hash . '-' . substr($sku, strlen($sku) - $savedSkuLength, $savedSkuLength);
            }
        }

        $preparedData['sku'] = trim(strip_tags($sku));

        $preparedData['price'] = $this->getNewProductPrice($rawData);
        $preparedData['qty'] = $rawData['qty'] > 0 ? (int)$rawData['qty'] : 1;

        $preparedData['images'] = $this->getNewProductImages($rawData);

        return $preparedData;
    }

    protected function getNewProductPrice(array $itemData): float
    {
        $currencyModel = $this->currencyFactory->create();
        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        $baseCurrencies = $currencyModel->getConfigBaseCurrencies();

        $isCurrencyAllowed = in_array($itemData['price_currency'], $allowedCurrencies);

        if ($isCurrencyAllowed && in_array($itemData['price_currency'], $baseCurrencies)) {
            return (float)$itemData['price'];
        }

        if (!$isCurrencyAllowed && !in_array($itemData['converted_price_currency'], $allowedCurrencies)) {
            return (float)$itemData['price'];
        }

        if (!$isCurrencyAllowed && in_array($itemData['converted_price_currency'], $baseCurrencies)) {
            return (float)$itemData['converted_price'];
        }

        $price = $isCurrencyAllowed ? $itemData['price'] : $itemData['converted_price_currency'];
        $currency = $isCurrencyAllowed ? $itemData['price_currency'] : $itemData['converted_price_currency'];

        $convertRate = $this->currencyFactory->create()->load($baseCurrencies[0])->getAnyRate($currency);
        $convertRate <= 0 && $convertRate = 1;

        return round($price / $convertRate, 2);
    }

    /**
     * @param array $itemData
     *
     * @return array
     */
    protected function getNewProductImages(array $itemData): array
    {
        if (empty($itemData['pictureUrl'])) {
            return [];
        }

        try {
            $destinationFolder = $this->createDestinationFolder($itemData['title']);
        } catch (\Exception $e) {
            return [];
        }

        $images = [];
        $imageCounter = 1;

        $mediaPath = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath();

        foreach ($itemData['pictureUrl'] as $url) {
            preg_match('/\.(jpg|jpeg|png|gif)/', $url, $matches);

            $extension = $matches[0] ?? '.jpg';
            $imagePath = $destinationFolder
                . DIRECTORY_SEPARATOR
                . \M2E\TikTokShop\Helper\Data::convertStringToSku($itemData['title']);
            $imagePath .= '-' . $imageCounter . $extension;

            try {
                $this->downloadImage($url, $imagePath);
            } catch (\Exception $e) {
                continue;
            }

            $images[] = str_replace($mediaPath . $this->productMediaConfig->getBaseTmpMediaPath(), '', $imagePath);
            $imageCounter++;
        }

        return $images;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \M2E\TikTokShop\Model\Exception
     */
    protected function createDestinationFolder($itemTitle): string
    {
        $baseTmpImageName = \M2E\TikTokShop\Helper\Data::convertStringToSku($itemTitle);

        $destinationFolder = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath()
            . $this->productMediaConfig->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR;

        $destinationFolder .= $baseTmpImageName[0] . DIRECTORY_SEPARATOR . $baseTmpImageName[1];

        if (
            !($this->fileDriver->isDirectory($destinationFolder)
                || $this->fileDriver->createDirectory($destinationFolder))
        ) {
            throw new \M2E\TikTokShop\Model\Exception("Unable to create directory '$destinationFolder'.");
        }

        return $destinationFolder;
    }

    //########################################

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function downloadImage($url, $imagePath)
    {
        $fileHandler = fopen($imagePath, 'w+');
        // ---------------------------------------

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $url);

        curl_setopt($curlHandler, CURLOPT_FILE, $fileHandler);
        curl_setopt($curlHandler, CURLOPT_REFERER, $url);
        curl_setopt($curlHandler, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curlHandler, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, 30);

        curl_exec($curlHandler);
        curl_close($curlHandler);

        fclose($fileHandler);
        // ---------------------------------------

        $imageInfo = $this->fileDriver->isFile($imagePath) ? getimagesize($imagePath) : null;

        if (empty($imageInfo)) {
            throw new \M2E\TikTokShop\Model\Exception("Image $url was not downloaded.");
        }
    }

    //########################################
}
