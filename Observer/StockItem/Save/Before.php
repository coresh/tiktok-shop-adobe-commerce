<?php

namespace M2E\TikTokShop\Observer\StockItem\Save;

class Before extends \M2E\TikTokShop\Observer\StockItem\AbstractStockItem
{
    public function beforeProcess(): void
    {
        parent::beforeProcess();
        $this->clearStoredStockItem();
    }

    public function afterProcess(): void
    {
        parent::afterProcess();
        $this->storeStockItem();
    }

    // ---------------------------------------

    protected function process(): void
    {
        if ($this->isAddingStockItemProcess()) {
            return;
        }

        $this->reloadStockItem();
    }

    protected function isAddingStockItemProcess(): bool
    {
        return $this->getStockItemId() <= 0;
    }

    private function clearStoredStockItem(): void
    {
        if ($this->isAddingStockItemProcess()) {
            return;
        }

        $key = $this->getStockItemId() . '_' . $this->getStoreId();
        $this->getRegistry()->unregister($key);
    }

    private function storeStockItem(): void
    {
        if ($this->isAddingStockItemProcess()) {
            return;
        }

        $key = $this->getStockItemId() . '_' . $this->getStoreId();
        $this->getRegistry()->register($key, $this->getStockItem());
    }
}
