<?php

namespace M2E\TikTokShop\Model\ResourceModel\Order;

class Change extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ORDER_ID = 'order_id';
    public const COLUMN_ACTION = 'action';
    public const COLUMN_PARAMS = 'params';
    public const COLUMN_CREATOR_TYPE = 'creator_type';
    public const COLUMN_PROCESSING_ATTEMPT_COUNT = 'processing_attempt_count';
    public const COLUMN_PROCESSING_ATTEMPT_DATE = 'processing_attempt_date';
    public const COLUMN_HASH = 'hash';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ORDER_CHANGE,
            self::COLUMN_ID
        );
    }
}
