<?php

namespace M2E\TikTokShop\Model\ResourceModel\Lock\Transactional;

/**
 * Class \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional\Collection
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Model\Lock\Transactional::class,
            \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::class
        );
    }

    //########################################
}
