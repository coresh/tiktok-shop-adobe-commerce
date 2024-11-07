<?php

namespace M2E\TikTokShop\Model\ResourceModel\Processing\PartialData;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Processing\PartialData::class,
            \M2E\TikTokShop\Model\ResourceModel\Processing\PartialData::class
        );
    }
}
