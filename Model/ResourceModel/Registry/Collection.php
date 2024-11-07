<?php

namespace M2E\TikTokShop\Model\ResourceModel\Registry;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Model\Registry::class,
            \M2E\TikTokShop\Model\ResourceModel\Registry::class
        );
    }
}
