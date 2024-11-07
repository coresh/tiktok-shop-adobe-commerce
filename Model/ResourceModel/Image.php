<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Image extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_URL = 'url';
    public const COLUMN_HASH = 'hash';
    public const COLUMN_URI = 'uri';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_IMAGE,
            self::COLUMN_ID
        );
    }
}
