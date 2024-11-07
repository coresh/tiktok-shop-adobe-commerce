<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class Warehouse extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_EFFECT_STATUS = 'effect_status';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_SUB_TYPE = 'sub_type';
    public const COLUMN_IS_DEFAULT = 'is_default';
    public const COLUMN_ADDRESS = 'address';
    public const COLUMN_SHIPPING_PROVIDER_MAPPING = 'shipping_provider_mapping';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_WAREHOUSE, self::COLUMN_ID);
    }
}
