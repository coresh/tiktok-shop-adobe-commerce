<?php

namespace M2E\TikTokShop\Model\ResourceModel\Wizard;

/**
 * Class \M2E\TikTokShop\Model\ResourceModel\Wizard\Collection
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    protected function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Model\Wizard::class,
            \M2E\TikTokShop\Model\ResourceModel\Wizard::class
        );
    }

    //########################################
}
