<?php

namespace M2E\TikTokShop\Model\ResourceModel\Processing;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Model\Processing::class,
            \M2E\TikTokShop\Model\ResourceModel\Processing::class
        );
    }
}
