<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class Shop extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_SHOP_NAME = 'shop_name';
    public const COLUMN_REGION = 'region';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_ORDER_LAST_SYNC = 'orders_last_synchronization';
    public const COLUMN_INVENTORY_LAST_SYNC = 'inventory_last_synchronization';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SHOP, self::COLUMN_ID);
    }
}
