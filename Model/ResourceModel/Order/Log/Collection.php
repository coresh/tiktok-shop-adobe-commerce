<?php

namespace M2E\TikTokShop\Model\ResourceModel\Order\Log;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    // ----------------------------------------

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Order\Log::class,
            \M2E\TikTokShop\Model\ResourceModel\Order\Log::class
        );
    }

    // ----------------------------------------

    /**
     * GroupBy fix
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $originSelect = clone $this->getSelect();
        $originSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $originSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $originSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $originSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $originSelect->columns(['*']);

        $countSelect = clone $originSelect;
        $countSelect->reset();
        $countSelect->from($originSelect, null);
        $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));

        return $countSelect;
    }

    public function createdDateGreaterThenOrEqual(\DateTime $date): Collection
    {
        $this->addFieldToFilter('main_table.create_date', [
            'gteq' => $date->format('Y-m-d H:i:s'),
        ]);

        return $this;
    }

    //########################################
}
