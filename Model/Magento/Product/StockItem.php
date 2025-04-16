<?php

namespace M2E\TikTokShop\Model\Magento\Product;

class StockItem
{
    private \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration;
    private \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem;
    private \Magento\CatalogInventory\Model\Indexer\Stock\Processor $indexStockProcessor;
    private \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider;
    private \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository;
    private \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage;
    private \M2E\Core\Helper\Magento\Stock $magentoStockHelper;
    private bool $stockStatusChanged = false;

    public function __construct(
        \M2E\Core\Helper\Magento\Stock $magentoStockHelper,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $indexStockProcessor,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->indexStockProcessor = $indexStockProcessor;
        $this->stockStateProvider = $stockStateProvider;
        $this->stockItem = $stockItem;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockRegistryStorage = $stockRegistryStorage;
        $this->magentoStockHelper = $magentoStockHelper;
    }

    public function getStockItem(): \Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->stockItem)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Stock Item is not set.');
        }

        return $this->stockItem;
    }

    public function subtractQty($qty, $save = true)
    {
        if (!$this->canChangeQty()) {
            return false;
        }

        if (!$this->isAllowedQtyBelowZero() && $this->resultOfSubtractingQtyBelowZero($qty)) {
            return false;
        }

        $stockItem = $this->getStockItem();

        if ($stockItem->getManageStock() && $this->stockConfiguration->canSubtractQty()) {
            $stockItem->setQty($stockItem->getQty() - $qty);
        }

        if (!$this->stockStateProvider->verifyStock($stockItem)) {
            $this->stockStatusChanged = true;
        }

        if ($save) {
            $this->stockItemRepository->save($stockItem);
            $this->afterSave();
        }

        return true;
    }

    public function resultOfSubtractingQtyBelowZero($qty)
    {
        return $this->getStockItem()->getQty() - $this->getStockItem()->getMinQty() - $qty < 0;
    }

    public function isAllowedQtyBelowZero()
    {
        $backordersStatus = $this->getStockItem()->getBackorders();

        return $backordersStatus == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY ||
            $backordersStatus == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY;
    }

    /**
     * @param $qty
     * @param bool $save
     *
     * @return bool
     */
    public function addQty($qty, $save = true)
    {
        if (!$this->canChangeQty()) {
            return false;
        }

        $stockItem = $this->getStockItem();
        $stockItem->setQty($stockItem->getQty() + $qty);

        if ($stockItem->getQty() > $stockItem->getMinQty()) {
            $stockItem->setIsInStock(true);
            $this->stockStatusChanged = true;
        }

        if ($save) {
            $this->stockItemRepository->save($stockItem);
            $this->afterSave();
        }

        return true;
    }

    //########################################

    public function afterSave()
    {
        if ($this->indexStockProcessor->isIndexerScheduled()) {
            $this->indexStockProcessor->reindexRow($this->getStockItem()->getProductId(), true);
        }

        if ($this->stockStatusChanged) {
            $this->stockRegistryStorage->removeStockStatus($this->getStockItem()->getProductId());
        }
    }

    //----------------------------------------

    public function isStockStatusChanged()
    {
        return $this->stockStatusChanged;
    }

    //########################################

    /**
     * @return bool
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function canChangeQty()
    {
        return $this->magentoStockHelper->canSubtractQty() && $this->getStockItem()->getManageStock();
    }
}
