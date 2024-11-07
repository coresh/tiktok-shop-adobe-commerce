<?php

namespace M2E\TikTokShop\Observer\Order;

class Quote extends \M2E\TikTokShop\Observer\AbstractObserver
{
    private \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory;
    private \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry;
    private ?\Magento\Catalog\Model\Product $product = null;
    private ?\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem = null;
    private \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private ?\M2E\TikTokShop\Model\Product\AffectedProduct\Collection $affectedProductCollection = null;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);

        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockRegistry = $stockRegistry;
        $this->listingLogService = $listingLogService;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function beforeProcess(): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->getEvent()->getItem();

        $product = $quoteItem->getProduct();

        if (!($product instanceof \Magento\Catalog\Model\Product) || $product->getId() <= 0) {
            throw new \M2E\TikTokShop\Model\Exception('Product ID should be greater than 0.');
        }

        $this->product = $product;
    }

    protected function process(): void
    {
        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function processQty(): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->getEvent()->getItem();

        if ($quoteItem->getHasChildren()) {
            return;
        }

        $oldValue = (int)$this->getStockItem()->getQty();
        $newValue = $oldValue - (int)$quoteItem->getTotalQty();

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                $oldValue,
                $newValue
            );
        }
    }

    private function processStockAvailability(): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $this->getEvent()->getItem();

        if ($quoteItem->getHasChildren()) {
            return;
        }

        $oldQty = (int)$this->getStockItem()->getQty();
        $newQty = $oldQty - (int)$quoteItem->getTotalQty();

        $oldValue = (bool)$this->getStockItem()->getIsInStock();
        $newValue = !($newQty <= (int)$this->stockItemFactory->create()->getMinQty());

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                $oldValue,
                $newValue
            );
        }
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getMagentoProduct(): \Magento\Catalog\Model\Product
    {
        if (!($this->product instanceof \Magento\Catalog\Model\Product)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Property "Product" should be set first.');
        }

        return $this->product;
    }

    private function getStockItem(): \Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        if ($this->stockItem !== null) {
            return $this->stockItem;
        }

        $stockItem = $this->stockRegistry->getStockItem(
            $this->getMagentoProduct()->getId(),
            $this->getMagentoProduct()->getStore()->getWebsiteId()
        );

        return $this->stockItem = $stockItem;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function addListingProductInstructions(): void
    {
        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $affectedProduct->getProduct(),
                $affectedProduct->getProduct()->getDescriptionTemplate()
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }

    private function areThereAffectedItems(): bool
    {
        return !$this->getAffectedProductCollection()->isEmpty();
    }

    private function getAffectedProductCollection(): \M2E\TikTokShop\Model\Product\AffectedProduct\Collection
    {
        if ($this->affectedProductCollection !== null) {
            return $this->affectedProductCollection;
        }

        return $this->affectedProductCollection = $this
            ->listingProductRepository
            ->getListingProductsByMagentoProductId($this->getMagentoProduct()->getId());
    }

    private function logListingProductMessage(
        \M2E\TikTokShop\Model\Product\AffectedProduct\Product $affectedProduct,
        $action,
        $oldValue,
        $newValue
    ): void {
        if ($affectedProduct->isAffectedVariant()) {
            $description = \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                'SKU: "%sku%"; From [%from%] to [%to%].',
                ['!sku' => $affectedProduct->getVariant()->getSku(), '!from' => $oldValue, '!to' => $newValue]
            );
        } else {
            $description = \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                'From [%from%] to [%to%].',
                ['!from' => $oldValue, '!to' => $newValue]
            );
        }

        $this->listingLogService->addProduct(
            $affectedProduct->getProduct(),
            \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            $description,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO
        );
    }
}
