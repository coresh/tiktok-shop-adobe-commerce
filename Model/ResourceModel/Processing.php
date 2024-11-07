<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class Processing extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_SERVER_HASH = 'server_hash';
    public const COLUMN_STAGE = 'stage';
    public const COLUMN_HANDLER_NICK = 'handler_nick';
    public const COLUMN_PARAMS = 'params';
    public const COLUMN_RESULT_DATA = 'result_data';
    public const COLUMN_RESULT_MESSAGES = 'result_messages';
    public const COLUMN_DATA_NEXT_PART = 'data_next_part';
    public const COLUMN_IS_COMPLETED = 'is_completed';
    public const COLUMN_EXPIRATION_DATE = 'expiration_date';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING,
            self::COLUMN_ID
        );
    }
}
