<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class OperationHistory extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_NICK = 'nick';
    public const COLUMN_PARENT_ID = 'parent_id';
    public const COLUMN_INITIATOR = 'initiator';
    public const COLUMN_START_DATE = 'start_date';
    public const COLUMN_END_DATE = 'end_date';
    public const COLUMN_DATA = 'data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
            self::COLUMN_ID
        );
    }
}
