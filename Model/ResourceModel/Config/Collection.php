<?php

namespace M2E\TikTokShop\Model\ResourceModel\Config;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Model\Config::class,
            \M2E\TikTokShop\Model\ResourceModel\Config::class
        );
    }
}
