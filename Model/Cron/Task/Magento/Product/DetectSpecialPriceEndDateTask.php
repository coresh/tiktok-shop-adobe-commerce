<?php

namespace M2E\TikTokShop\Model\Cron\Task\Magento\Product;

class DetectSpecialPriceEndDateTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'magento/product/detect_special_price_end_date';

    /** @var \M2E\TikTokShop\PublicServices\Product\SqlChange */
    private \M2E\TikTokShop\PublicServices\Product\SqlChange $publicService;
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $catalogProductCollectionFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory */
    private $listingCollectionFactory;
    private \M2E\TikTokShop\Model\Registry\Manager $registry;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registry,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogProductCollectionFactory,
        \M2E\TikTokShop\PublicServices\Product\SqlChange $publicService
    ) {
        $this->publicService = $publicService;
        $this->catalogProductCollectionFactory = $catalogProductCollectionFactory;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    public function process($context): void
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

        /**
         * @psalm-suppress UndefinedClass
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
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

    private function setLastProcessedProductId(int $magentoProductId)
    {
        $this->registry->setValue(
            '/magento/product/detect_special_price_end_date/last_magento_product_id/',
            (string)$magentoProductId
        );
    }
}
