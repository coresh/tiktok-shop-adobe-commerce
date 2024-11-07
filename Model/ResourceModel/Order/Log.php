<?php

namespace M2E\TikTokShop\Model\ResourceModel\Order;

class Log extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ORDER_LOG, 'id');
    }
}
