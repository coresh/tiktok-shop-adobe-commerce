<?php

namespace M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

abstract class AbstractModel extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    private \M2E\Core\Helper\Magento\Staging $magentoStagingHelper;

    public function __construct(
        \M2E\Core\Helper\Magento\Staging $magentoStagingHelper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->magentoStagingHelper = $magentoStagingHelper;
    }

    // ----------------------------------------

    public function joinLeft($name, $cond, $cols = '*', $schema = null)
    {
        $cond = $this->replaceJoinCondition($name, $cond);
        $this->getSelect()->joinLeft($name, $cond, $cols, $schema);
    }

    public function joinInner($name, $cond, $cols = '*', $schema = null)
    {
        $cond = $this->replaceJoinCondition($name, $cond);
        $this->getSelect()->joinInner($name, $cond, $cols, $schema);
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    private function replaceJoinCondition($table, $cond)
    {
        if (
            $this->magentoStagingHelper->isInstalled()
            && $this->magentoStagingHelper->isStagedTable($table)
            && strpos($cond, 'entity_id') !== false
        ) {
            $linkField = $this->magentoStagingHelper->isStagedTable($table, ProductAttributeInterface::ENTITY_TYPE_CODE)
                ? $this->magentoStagingHelper->getTableLinkField(ProductAttributeInterface::ENTITY_TYPE_CODE)
                : $this->magentoStagingHelper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE);

            $cond = str_replace('entity_id', $linkField, $cond);
        }

        return $cond;
    }

    // ----------------------------------------

    protected function _getItemId(\Magento\Framework\DataObject $item)
    {
        $result = (int)parent::_getItemId($item);
        if ($result === 0) {
            return null;
        }

        return $result;
    }
}
