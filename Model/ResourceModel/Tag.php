<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Tag extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ERROR_CODE = 'error_code';
    public const COLUMN_TEXT = 'text';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TAG, self::COLUMN_ID);
    }
}
