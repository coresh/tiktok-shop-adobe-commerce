<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Promotion extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_PROMOTION_ID = 'promotion_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_PRODUCT_LEVEL = 'product_level';
    public const COLUMN_START_DATE = 'start_date';
    public const COLUMN_END_DATE = 'end_date';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROMOTION,
            self::COLUMN_ID
        );
    }
}
