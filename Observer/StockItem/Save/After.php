<?php

namespace M2E\TikTokShop\Observer\StockItem\Save;

class After extends \M2E\TikTokShop\Observer\StockItem\AbstractStockItem
{
    private \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private ?int $magentoProductId = null;
    private ?\M2E\TikTokShop\Model\Product\AffectedProduct\Collection $affectedProductCollection = null;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeProcessorFactory
    ) {
        parent::__construct($registry, $stockItemFactory);

        $this->changeAttributeTrackerFactory = $changeProcessorFactory;
        $this->listingLogService = $listingLogService;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();

        $productId = (int)$this->getStockItem()->getProductId();

        if ($productId <= 0) {
            throw new \M2E\TikTokShop\Model\Exception('Product ID should be greater than 0.');
        }

        $this->magentoProductId = $productId;

        $this->reloadStockItem();
    }

    protected function process(): void
    {
        if ($this->getStoredStockItem() === null) {
            return;
        }

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    private function areThereAffectedItems(): bool
    {
        return !$this->getAffectedProductCollection()->isEmpty();
    }

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

    private function processQty(): void
    {
        $oldValue = (int)$this->getStoredStockItem()->getOrigData('qty');
        $newValue = (int)$this->getStockItem()->getQty();

        if ($oldValue === $newValue) {
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
        $oldValue = (bool)$this->getStoredStockItem()->getOrigData('is_in_stock');
        $newValue = (bool)$this->getStockItem()->getIsInStock();

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue === $newValue) {
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

    private function getAffectedProductCollection(): \M2E\TikTokShop\Model\Product\AffectedProduct\Collection
    {
        if (!empty($this->affectedProductCollection)) {
            return $this->affectedProductCollection;
        }

        return $this->affectedProductCollection = $this->listingProductRepository
            ->getListingProductsByMagentoProductId($this->getMagentoProductId());
    }

    private function getMagentoProductId(): int
    {
        return (int)$this->magentoProductId;
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
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            $description,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    private function getStoredStockItem(): ?\Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        $key = $this->getStockItemId() . '_' . $this->getStoreId();

        return $this->getRegistry()->registry($key);
    }
}
