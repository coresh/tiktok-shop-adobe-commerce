<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class Logger
{
    private array $logs = [];
    private \M2E\TikTokShop\Model\Image\Repository $imageRepository;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private string $onlineTitle;
    private string $onlineDescription;
    private string $onlineMainCategory;
    private string $onlineCategoryData;
    private string $onlineBrandId;
    private string $onlineBrandName;
    private array $variantOnlineData;

    public function __construct(
        \M2E\TikTokShop\Model\Image\Repository $imageRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->imageRepository = $imageRepository;
    }

    public function saveProductDataBeforeUpdate(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->onlineTitle = $product->getOnlineTitle();
        $this->onlineDescription = $product->getOnlineDescription();
        $this->onlineMainCategory = $product->getOnlineMainCategory();
        $this->onlineCategoryData = $product->getOnlineCategoryData();
        $this->onlineBrandId = $product->getOnlineBrandId();
        $this->onlineBrandName = $product->getOnlineBrandName();
        $this->variantOnlineData = $product->getVariantOnlineData();
    }

    public function calculateLogs(\M2E\TikTokShop\Model\Product $product, array $requestMetadata): array
    {
        if (isset($requestMetadata[DataBuilder\VariantSku::NICK])) {
            $this->processSuccessRevisePrice($product);
            $this->processSuccessReviseQty($product);
        }

        $this->processSuccessFoundBrand($product);

        $this->processTitleLogs($product);

        $this->processDescriptionLogs($product);

        $this->processImagesLogs($product, $requestMetadata);

        $this->processCategoriesLogs($product, $requestMetadata);

        return $this->logs;
    }

    private function processSuccessRevisePrice(\M2E\TikTokShop\Model\Product $product): void
    {
        $beforeData = [];
        foreach ($this->variantOnlineData as $onlineData) {
            $beforeData[$onlineData->getVariantId()] = $onlineData;
        }

        $currencyCode = $product->getListing()->getShop()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);
        foreach ($product->getVariants() as $variant) {
            $newOnlineData = $variant->getOnlineData();
            $from = 'N/A';
            if (isset($beforeData[$newOnlineData->getVariantId()])) {
                $from = $beforeData[$newOnlineData->getVariantId()]->getPrice();
            }

            if ($from === $newOnlineData->getPrice()) {
                continue;
            }

            if ($product->isSimple()) {
                $message = sprintf(
                    'Product Price was revised from %s to %s',
                    $currency->toCurrency($from),
                    $currency->toCurrency($newOnlineData->getPrice()),
                );
            } else {
                $message = sprintf(
                    'SKU %s: Price was revised from %s to %s',
                    $newOnlineData->getSku(),
                    $currency->toCurrency($from),
                    $currency->toCurrency($newOnlineData->getPrice()),
                );
            }

            $this->createSuccessMessage($message);
        }
    }

    private function processSuccessReviseQty(\M2E\TikTokShop\Model\Product $product): void
    {
        $beforeData = [];
        foreach ($this->variantOnlineData as $onlineData) {
            $beforeData[$onlineData->getVariantId()] = $onlineData;
        }

        foreach ($product->getVariants() as $variant) {
            $newOnlineData = $variant->getOnlineData();
            $from = 'N/A';
            if (isset($beforeData[$newOnlineData->getVariantId()])) {
                $from = $beforeData[$newOnlineData->getVariantId()]->getQty();
            }

            if ($from === $newOnlineData->getQty()) {
                continue;
            }

            if ($product->isSimple()) {
                $message = sprintf(
                    'Product QTY was revised from %s to %s',
                    $from,
                    $newOnlineData->getQty()
                );
            } else {
                $message = sprintf(
                    'SKU %s: QTY was revised from %s to %s',
                    $newOnlineData->getSku(),
                    $from,
                    $newOnlineData->getQty()
                );
            }

            $this->createSuccessMessage($message);
        }
    }

    private function processSuccessFoundBrand(\M2E\TikTokShop\Model\Product $product): void
    {
        $newBrandId = $product->getOnlineBrandId();
        $newBrandName = $product->getOnlineBrandName();
        $channelTitle = \M2E\TikTokShop\Helper\Module::getChannelTitle();

        if (empty($this->onlineBrandId) && !empty($newBrandId)) {
            $message = sprintf(
                'Brand "%s" was assigned to the Product on %s',
                $newBrandName,
                $channelTitle
            );
            $this->createSuccessMessage($message);
        }

        if (!empty($this->onlineBrandId) && !empty($newBrandId) && $this->onlineBrandId !== $newBrandId) {
            $message = sprintf('Brand was changed from "%s" to "%s"', $this->onlineBrandName, $newBrandName);
            $this->createSuccessMessage($message);
        }
    }

    private function processTitleLogs(\M2E\TikTokShop\Model\Product $product): void
    {
        if ($product->getOnlineTitle() !== $this->onlineTitle) {
            $message = 'Item was revised: Product Title was updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function processDescriptionLogs(\M2E\TikTokShop\Model\Product $product): void
    {
        if ($product->getOnlineDescription() !== $this->onlineDescription) {
            $message = 'Item was revised: Product Description was updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function processImagesLogs(\M2E\TikTokShop\Model\Product $product, array $requestMetadata): void
    {
        if (!isset($requestMetadata[DataBuilder\Images::NICK])) {
            return;
        }

        $images = $this->imageRepository->findImagesWithProductRelation(
            $product->getId(),
            \M2E\TikTokShop\Model\Image::IMAGE_TYPE_PRODUCT,
        );

        $existsUri = [];
        foreach ($images as $image) {
            $existsUri[] = $image->getUri();
        }

        $imagesMetaData = $requestMetadata[DataBuilder\Images::NICK];

        if (empty($images) && empty($imagesMetaData)) {
            return;
        }

        $message = 'Item was revised: Product Images were updated.';
        foreach ($imagesMetaData as $productImage) {
            if ($productImage['uri'] === null || !in_array($productImage['uri'], $existsUri, true)) {
                $this->createSuccessMessage($message);
                return;
            }
        }
    }

    private function processCategoriesLogs(\M2E\TikTokShop\Model\Product $product, array $requestMetadata): void
    {
        $categoryUpdated = false;
        $sizeChartUpdated = false;
        $certificatesUpdated = false;

        if (isset($requestMetadata[DataBuilder\Categories::NICK])) {
            if (
                $product->getOnlineMainCategory() !== $this->onlineMainCategory
                || $product->getOnlineCategoryData() !== $this->onlineCategoryData
            ) {
                $categoryUpdated = true;
            }
        }

        if (isset($requestMetadata[DataBuilder\SizeChart::NICK])) {
            $sizeChartUpdated = $this->isSizeChartUpdated($product, $requestMetadata[DataBuilder\SizeChart::NICK]);
        }

        if (isset($requestMetadata[DataBuilder\CertificateImage::NICK])) {
            $certificatesUpdated = $this->isCertificatesUpdated($product, $requestMetadata[DataBuilder\CertificateImage::NICK]);
        }

        if ($categoryUpdated || $sizeChartUpdated || $certificatesUpdated) {
            $message = 'Item was revised: Product Categories were updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function isSizeChartUpdated(\M2E\TikTokShop\Model\Product $product, array $sizeChartMetadata): bool
    {
        $images = $this->imageRepository->findImagesWithProductRelation(
            $product->getId(),
            \M2E\TikTokShop\Model\Image::IMAGE_TYPE_SIZE_CHART,
        );

        if (empty($images) && !isset($sizeChartMetadata['image']['uri'])) {
            return false;
        }

        $existImage = reset($images);

        if ($existImage->getUri() === null) {
            return true;
        }

        return $sizeChartMetadata['image']['uri'] !== $existImage->getUri();
    }

    private function isCertificatesUpdated(\M2E\TikTokShop\Model\Product $product, array $certificatesMetadata): bool
    {
        $existedImages = $this->imageRepository->findImagesWithProductRelation(
            $product->getId(),
            \M2E\TikTokShop\Model\Image::IMAGE_TYPE_CERTIFICATE,
        );

        $existedUri = [];
        foreach ($existedImages as $image) {
            $existedUri[] = $image->getUri();
        }

        foreach ($certificatesMetadata as $certificateData) {
            foreach ($certificateData['images'] as $certificateImageData) {
                if ($certificateImageData['uri'] === null) {
                    return true;
                }

                if (!in_array($certificateImageData['uri'], $existedUri, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function createSuccessMessage(string $message): void
    {
        $this->logs[] = \M2E\Core\Model\Response\Message::createSuccess($message);
    }
}
