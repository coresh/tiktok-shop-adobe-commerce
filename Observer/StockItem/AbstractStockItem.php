<?php

namespace M2E\TikTokShop\Observer\StockItem;

abstract class AbstractStockItem extends \M2E\TikTokShop\Observer\AbstractObserver
{
    private \Magento\Framework\Registry $registry;
    private \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory;
    private \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem;
    private int $stockItemId;
    private int $storeId;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        $this->registry = $registry;
        $this->stockItemFactory = $stockItemFactory;
        parent::__construct($helperFactory);
    }

    public function beforeProcess(): void
    {
        $stockItem = $this->getEventObserver()->getData('item');

        if (!($stockItem instanceof \Magento\CatalogInventory\Api\Data\StockItemInterface)) {
            throw new \M2E\TikTokShop\Model\Exception('StockItem event doesn\'t have correct StockItem instance.');
        }

        $this->stockItem = $stockItem;

        $this->stockItemId = (int)$this->stockItem->getId();
        $this->storeId = (int)$this->stockItem->getData('store_id');
    }

    // ----------------------------------------

    protected function getStockItem(): \Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->stockItem)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Property "StockItem" should be set first.');
        }

        return $this->stockItem;
    }

    protected function reloadStockItem(): \Magento\CatalogInventory\Api\Data\StockItemInterface
    {
        if ($this->getStockItemId() <= 0) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                'To reload StockItem instance stockitem_id should be greater than 0.'
            );
        }

        $this->stockItem = $this->stockItemFactory->create()
                                                  ->setStoreId($this->getStoreId())
                                                  ->load($this->getStockItemId());

        return $this->getStockItem();
    }

    // ---------------------------------------

    protected function getStockItemId(): int
    {
        return $this->stockItemId;
    }

    protected function getStoreId(): int
    {
        return $this->storeId;
    }

    protected function getRegistry(): \Magento\Framework\Registry
    {
        return $this->registry;
    }
}
