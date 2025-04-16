<?php

namespace M2E\TikTokShop\Plugin\MSI\Magento\InventoryCatalog\Model\ResourceModel;

class SetDataToLegacyStockItem extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    public const PRODUCTS_FOR_REINDEX_REGISTRY_KEY = 'msi_products_for_reindex';

    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalData;
    /** @var \Magento\Catalog\Model\ResourceModel\Product */
    private $productResource;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->globalData = $globalData;
        $this->productResource = $productResource;
    }

    /**
     * @param $interceptor
     * @param \Closure $callback
     * @param mixed ...$arguments
     *
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    /**
     * @param $interceptor
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);
        if (!isset($arguments[0])) {
            return $result;
        }

        $productIds = (array)$this->globalData->getValue(self::PRODUCTS_FOR_REINDEX_REGISTRY_KEY);
        $productIds[] = (int)$this->productResource->getIdBySku($arguments[0]);

        $this->globalData->unsetValue(self::PRODUCTS_FOR_REINDEX_REGISTRY_KEY);
        $this->globalData->setValue(self::PRODUCTS_FOR_REINDEX_REGISTRY_KEY, $productIds);

        return $result;
    }

    //########################################
}
