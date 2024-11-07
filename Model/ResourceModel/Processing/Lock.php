<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Processing;

class Lock extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PROCESSING_ID = 'processing_id';
    public const COLUMN_OBJECT_NICK = 'object_nick';
    public const COLUMN_OBJECT_ID = 'object_id';
    public const COLUMN_TAG = 'tag';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_LOCK,
            self::COLUMN_ID
        );
    }
}
