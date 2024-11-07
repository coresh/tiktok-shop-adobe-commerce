<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Processing\Lock;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Processing\Lock::class,
            \M2E\TikTokShop\Model\ResourceModel\Processing\Lock::class
        );
    }
}
