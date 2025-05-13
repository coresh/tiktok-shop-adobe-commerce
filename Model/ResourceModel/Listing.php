<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Listing extends ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_STORE_ID = 'store_id';
    public const COLUMN_TEMPLATE_DESCRIPTION_ID = 'template_description_id';
    public const COLUMN_TEMPLATE_SELLING_FORMAT_ID = 'template_selling_format_id';
    public const COLUMN_TEMPLATE_SYNCHRONIZATION_ID = 'template_synchronization_id';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LISTING,
            self::COLUMN_ID
        );
    }
}
