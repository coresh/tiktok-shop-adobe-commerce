<?php

namespace M2E\TikTokShop\Model\Magento\Product\Inventory;

use M2E\TikTokShop\Model\Exception;

abstract class AbstractModel
{
    private \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry;
    private \Magento\Catalog\Model\Product $product;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws Exception
     */
    public function getProduct()
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->product)) {
            throw new Exception('Catalog Product Model is not set');
        }

        return $this->product;
    }

    public function isStockAvailability(): bool
    {
        return $this->magentoProductHelper->calculateStockAvailability(
            $this->isInStock(),
            $this->getStockItem()->getManageStock(),
            $this->getStockItem()->getUseConfigManageStock()
        );
    }

    /**
     * @return mixed
     */
    abstract public function isInStock();

    /**
     * @return mixed
     */
    abstract public function getQty();

    /**
     * @param bool $withScope
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws Exception
     */
    public function getStockItem($withScope = true)
    {
        return $this->stockRegistry->getStockItem(
            $this->getProduct()->getId(),
            $withScope ? $this->getProduct()->getStore()->getWebsiteId() : null
        );
    }
}
