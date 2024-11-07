<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Listing\Wizard;

class Step extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_WIZARD_ID = 'wizard_id';
    public const COLUMN_NICK = 'nick';
    public const COLUMN_DATA = 'data';
    public const COLUMN_IS_COMPLETED = 'is_completed';
    public const COLUMN_IS_SKIPPED = 'is_skipped';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_STEP, self::COLUMN_ID);
    }
}
