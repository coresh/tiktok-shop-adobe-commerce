<?php

/*
    // $this->_objectManager instanceof \Magento\Framework\ObjectManagerInterface
    $model = $this->_objectManager->create('\M2E\TikTokShop\PublicServices\Product\ObjectChange');

    // you have a product ID for observing
    $model->observeProduct(561);

    // you have '\Magento\Catalog\Model\Product' object for observing
    $product = $this->productFactory->create();
    $product->load(561);

    $model->observeProduct($product);

    // make changes for these products by direct sql
    $model->applyChanges();
*/

namespace M2E\TikTokShop\PublicServices\Product;

use Magento\Framework\Event\Observer;

class ObjectChange
{
    public const VERSION = '1.0.1';

    /** @var \Magento\Catalog\Model\ProductFactory */
    private $productFactory;
    /** @var \Magento\CatalogInventory\Api\StockRegistryInterface */
    private $stockRegistry;

    /** @var \M2E\TikTokShop\Observer\Product\AddUpdate\BeforeFactory */
    private $observerProductSaveBeforeFactory;
    /** @var \M2E\TikTokShop\Observer\Product\AddUpdate\AfterFactory */
    private $observerProductSaveAfterFactory;
    /** @var \M2E\TikTokShop\Observer\StockItem\Save\BeforeFactory */
    private $observerStockItemSaveBeforeFactory;
    /** @var \M2E\TikTokShop\Observer\StockItem\Save\AfterFactory */
    private $observerStockItemSaveAfterFactory;

    /** @var array */
    protected $productObservers = [];
    /** @var array */
    protected $stockItemObservers = [];

    /** @var \M2E\TikTokShop\PublicServices\Product\SqlChange */
    protected $sqlChange;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \M2E\TikTokShop\Observer\Product\AddUpdate\BeforeFactory $observerProductSaveBeforeFactory,
        \M2E\TikTokShop\Observer\Product\AddUpdate\AfterFactory $observerProductSaveAfterFactory,
        \M2E\TikTokShop\Observer\StockItem\Save\BeforeFactory $observerStockItemSaveBeforeFactory,
        \M2E\TikTokShop\Observer\StockItem\Save\AfterFactory $observerStockItemSaveAfterFactory,
        SqlChange $sqlChange,
        \M2E\TikTokShop\Helper\Magento $magentoHelper
    ) {
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->sqlChange = $sqlChange;

        $this->observerProductSaveBeforeFactory = $observerProductSaveBeforeFactory;
        $this->observerProductSaveAfterFactory = $observerProductSaveAfterFactory;
        $this->observerStockItemSaveBeforeFactory = $observerStockItemSaveBeforeFactory;
        $this->observerStockItemSaveAfterFactory = $observerStockItemSaveAfterFactory;
        $this->magentoHelper = $magentoHelper;
    }

    public function applyChanges()
    {
        if ($this->magentoHelper->isMSISupportingVersion()) {
            return $this->sqlChange->applyChanges();
        }

        foreach ($this->productObservers as $productObserver) {
            $this->observerProductSaveAfterFactory->create()->execute($productObserver);
        }

        foreach ($this->stockItemObservers as $stockItemObserver) {
            $this->observerStockItemSaveAfterFactory->create()->execute($stockItemObserver);
        }

        return $this->flushObservers();
    }

    /**
     * @return $this
     */
    public function flushObservers()
    {
        $this->productObservers = [];
        $this->stockItemObservers = [];

        return $this;
    }

    //########################################

    /**
     * @param \Magento\Catalog\Model\Product|int $product
     * @param int $storeId
     *
     * @return void
     */
    public function observeProduct($product, $storeId = 0): void
    {
        if ($this->magentoHelper->isMSISupportingVersion()) {
            $productId = $product instanceof \Magento\Catalog\Model\Product ? $product->getId() : (int)$product;

            $this->sqlChange->markProductChanged($productId);
            return;
        }

        if ($this->isProductObserved($product, $storeId)) {
            return;
        }

        if (!($product instanceof \Magento\Catalog\Model\Product)) {
            $model = $this->productFactory->create()->setStoreId($storeId);
            $model->load($product);
            $product = $model;
        }

        $key = $product->getId() . '##' . $storeId;

        $productObserver = $this->prepareProductObserver($product);
        $this->observerProductSaveBeforeFactory->create()->execute($productObserver);
        $this->productObservers[$key] = $productObserver;

        $stockItemObserver = $this->prepareStockItemObserver($product);
        $this->observerStockItemSaveBeforeFactory->create()->execute($stockItemObserver);
        $this->stockItemObservers[$key] = $stockItemObserver;
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $product
     * @param int $storeId
     *
     * @return bool
     */
    public function isProductObserved($product, $storeId = 0)
    {
        $productId = $product instanceof \Magento\Catalog\Model\Product ? $product->getId() : $product;
        $key = $productId . '##' . $storeId;

        if (
            array_key_exists($key, $this->productObservers) ||
            array_key_exists($key, $this->stockItemObservers)
        ) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    private function prepareProductObserver(\Magento\Catalog\Model\Product $product)
    {
        $data = ['product' => $product];

        $event = new \Magento\Framework\Event($data);

        $observer = new Observer();
        $observer->setData(array_merge(['event' => $event], $data));

        return $observer;
    }

    private function prepareStockItemObserver(\Magento\Catalog\Model\Product $product)
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );

        $data = ['object' => $stockItem];

        $event = new \Magento\Framework\Event($data);

        $observer = new Observer();
        $observer->setData(array_merge(['event' => $event], $data));

        return $observer;
    }
}
