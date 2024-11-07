<?php

namespace M2E\TikTokShop\Model\ResourceModel\OperationHistory;

/**
 * Class \M2E\TikTokShop\Model\ResourceModel\OperationHistory\Collection
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Model\OperationHistory::class,
            \M2E\TikTokShop\Model\ResourceModel\OperationHistory::class
        );
    }

    //########################################
}
