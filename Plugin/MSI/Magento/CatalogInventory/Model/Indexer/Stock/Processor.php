<?php

namespace M2E\TikTokShop\Plugin\MSI\Magento\CatalogInventory\Model\Indexer\Stock;

class Processor extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    public const PRODUCTS_FOR_REINDEX_REGISTRY_KEY = 'msi_products_for_reindex';

    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalData;
    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->globalData = $globalData;
        $this->indexerRegistry = $indexerRegistry;
    }

    //########################################

    protected function canExecute(): bool
    {
        /** @var \M2E\Core\Helper\Magento $helper */
        $magentoHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Core\Helper\Magento::class
        );

        if (!$magentoHelper->isMSISupportingVersion()) {
            return false;
        }

        return parent::canExecute();
    }

    /**
     * @param $interceptor
     * @param \Closure $callback
     * @param mixed ...$arguments
     *
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function aroundReindexList($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('reindexList', $interceptor, $callback, $arguments);
    }

    /**
     * @param $interceptor
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processReindexList($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);
        if (!isset($arguments[0])) {
            return $result;
        }

        $productIds = (array)$this->globalData->getValue(self::PRODUCTS_FOR_REINDEX_REGISTRY_KEY);
        $this->globalData->unsetValue(self::PRODUCTS_FOR_REINDEX_REGISTRY_KEY);

        if ($productIds !== $arguments[0]) {
            return $result;
        }

        if (isset($arguments[1]) && $arguments[1] === true) {
            return $result;
        }

        $indexer = $this->indexerRegistry->get(\Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID);
        if ($indexer->isScheduled()) {
            $indexer->reindexList($productIds);
        }

        return $result;
    }

    //########################################
}
