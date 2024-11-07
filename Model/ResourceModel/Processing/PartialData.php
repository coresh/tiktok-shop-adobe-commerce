<?php

namespace M2E\TikTokShop\Model\ResourceModel\Processing;

class PartialData extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PROCESSING_ID = 'processing_id';
    public const COLUMN_PART_NUMBER = 'part_number';
    public const COLUMN_DATA = 'data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_PARTIAL_DATA,
            self::COLUMN_ID
        );
    }
}
