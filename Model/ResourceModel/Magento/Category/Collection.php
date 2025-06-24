<?php

namespace M2E\TikTokShop\Model\ResourceModel\Magento\Category;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;

class Collection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    private \M2E\Core\Helper\Magento\Staging $magentoStagingHelper;

    public function __construct(
        \M2E\Core\Helper\Magento\Staging $magentoStagingHelper,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $connection
        );
        $this->magentoStagingHelper = $magentoStagingHelper;
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    public function joinTable($table, $bind, $fields = null, $cond = null, $joinType = 'inner')
    {
        if (
            $this->magentoStagingHelper->isInstalled()
            && $this->magentoStagingHelper->isStagedTable($table, CategoryAttributeInterface::ENTITY_TYPE_CODE)
            && strpos($bind, 'entity_id') !== false
        ) {
            $bind = str_replace(
                'entity_id',
                $this->magentoStagingHelper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE),
                $bind
            );
        }

        return parent::joinTable($table, $bind, $fields, $cond, $joinType);
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    public function joinAttribute($alias, $attribute, $bind, $filter = null, $joinType = 'inner', $storeId = null)
    {
        if ($this->magentoStagingHelper->isInstalled() && is_string($attribute) && is_string($bind)) {
            $attrArr = explode('/', $attribute);
            if (
                CategoryAttributeInterface::ENTITY_TYPE_CODE === $attrArr[0]
                && $bind === 'entity_id'
            ) {
                $bind = $this->magentoStagingHelper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE);
            }
        }

        return parent::joinAttribute($alias, $attribute, $bind, $filter, $joinType, $storeId);
    }
}
