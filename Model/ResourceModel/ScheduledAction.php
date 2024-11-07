<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class ScheduledAction extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_PRODUCT_ID = 'listing_product_id';
    public const COLUMN_ACTION_TYPE = 'action_type';
    public const COLUMN_STATUS_CHANGER = 'status_changer';
    public const COLUMN_IS_FORCE = 'is_force';
    public const COLUMN_TAG = 'tag';
    public const COLUMN_VARIANTS_SETTINGS = 'variants_settings';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION,
            self::COLUMN_ID,
        );
    }
}
