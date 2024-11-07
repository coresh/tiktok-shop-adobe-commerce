<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Lock;

class Item extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_NICK = 'nick';
    public const COLUMN_PARENT_ID = 'parent_id';
    public const COLUMN_DATA = 'data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LOCK_ITEM, self::COLUMN_ID);
    }
}
