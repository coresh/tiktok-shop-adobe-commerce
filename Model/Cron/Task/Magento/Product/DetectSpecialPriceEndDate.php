<?php

namespace M2E\TikTokShop\Model\Cron\Task\Magento\Product;

class DetectSpecialPriceEndDate extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'magento/product/detect_special_price_end_date';

    /** @var int (in seconds) */
    protected int $intervalInSeconds = 7200;

    /** @var \M2E\TikTokShop\PublicServices\Product\SqlChange */
    private \M2E\TikTokShop\PublicServices\Product\SqlChange $publicService;
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    private $catalogProductCollectionFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory */
    private $listingCollectionFactory;
    private \M2E\TikTokShop\Model\Registry\Manager $registry;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registry,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogProductCollectionFactory,
        \M2E\TikTokShop\PublicServices\Product\SqlChange $publicService,
        \M2E\TikTokShop\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource,
        );

        $this->publicService = $publicService;
        $this->catalogProductCollectionFactory = $catalogProductCollectionFactory;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        if ($this->getLastProcessedProductId() === null) {
            $this->setLastProcessedProductId(0);
        }

        $changedProductsPrice = $this->getAllChangedProductsPrice();

        if (!$changedProductsPrice) {
            $this->setLastProcessedProductId(0);

            return;
        }

        $variantSkus = $this->productRepository->findActiveVariantSkusByMagentoProductIds(
            array_keys($changedProductsPrice),
        );
        foreach ($variantSkus as $variantSku) {
            $currentPrice = $variantSku->getOnlineCurrentPrice();
            $newPrice = (float)$changedProductsPrice[$variantSku->getMagentoProductId()]['price'];

            if ($currentPrice === $newPrice) {
                continue;
            }

            $this->publicService->markPriceChanged($variantSku->getMagentoProductId());
        }

        $this->publicService->applyChanges();

        $lastMagentoProduct = $this->getArrayKeyLast($changedProductsPrice);
        $this->setLastProcessedProductId((int)$lastMagentoProduct);
    }

    private function getArrayKeyLast($array)
    {
        if (!is_array($array) || empty($array)) {
            return null;
        }

        $arrayKeys = array_keys($array);

        return $arrayKeys[count($array) - 1];
    }

    private function getAllStoreIds(): array
    {
        $storeIds = [];

        $collectionListing = $this->listingCollectionFactory->create();
        $collectionListing->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collectionListing->getSelect()->columns([
            \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_STORE_ID
            => \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_STORE_ID,
        ]);
        $collectionListing->getSelect()->group(\M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_STORE_ID);

        foreach ($collectionListing->getData() as $item) {
            $storeIds[] = $item[\M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_STORE_ID];
        }

        return $storeIds;
    }

    private function getChangedProductsPrice($storeId): array
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-1 day');

        $collection = $this->catalogProductCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect('price');
        $collection->addAttributeToFilter('special_price', ['notnull' => true]);
        $collection->addFieldToFilter('special_to_date', ['notnull' => true]);
        $collection->addFieldToFilter('special_to_date', ['lt' => $date->format('Y-m-d H:i:s')]);
        $collection->addFieldToFilter('entity_id', ['gt' => (int)$this->getLastProcessedProductId()]);
        $collection->setOrder('entity_id', 'asc');
        $collection->getSelect()->limit(1000);

        return $collection->getItems();
    }

    private function getAllChangedProductsPrice(): array
    {
        $changedProductsPrice = [];

        foreach ($this->getAllStoreIds() as $storeId) {
            /** @var \Magento\Catalog\Model\Product $magentoProduct */
            foreach ($this->getChangedProductsPrice($storeId) as $magentoProduct) {
                $changedProductsPrice[$magentoProduct->getId()] = [
                    'price' => $magentoProduct->getPrice(),
                ];
            }
        }

        ksort($changedProductsPrice);

        return array_slice($changedProductsPrice, 0, 1000, true);
    }

    private function getLastProcessedProductId()
    {
        return $this->registry->getValue(
            '/magento/product/detect_special_price_end_date/last_magento_product_id/'
        );
    }

    private function setLastProcessedProductId($magentoProductId)
    {
        $this->registry->setValue(
            '/magento/product/detect_special_price_end_date/last_magento_product_id/',
            (int)$magentoProductId
        );
    }
}
