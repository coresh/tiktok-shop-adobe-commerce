<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class ShippingProvider extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_DELIVERY_OPTION_ID = 'tts_delivery_option_id';
    public const COLUMN_SHIPPING_PROVIDER_ID = 'tts_shipping_provider_id';
    public const COLUMN_SHIPPING_PROVIDER_NAME = 'tts_shipping_provider_name';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SHIPPING_PROVIDERS,
            self::COLUMN_ID
        );
    }
}
