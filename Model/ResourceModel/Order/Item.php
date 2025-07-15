<?php

namespace M2E\TikTokShop\Model\ResourceModel\Order;

class Item extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ORDER_ID = 'order_id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_PRODUCT_DETAILS = 'product_details';
    public const COLUMN_QTY_RESERVED = 'qty_reserved';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_TTS_ITEM_ID = 'tts_item_id';
    public const COLUMN_ITEM_STATUS = 'item_status';
    public const COLUMN_TTS_PRODUCT_ID = 'tts_product_id';
    public const COLUMN_TTS_SKU_ID = 'tts_sku_id';
    public const COLUMN_PACKAGE_ID = 'package_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_QTY_PURCHASED = 'qty_purchased';
    public const COLUMN_SALE_PRICE = 'sale_price';
    public const COLUMN_ORIGINAL_PRICE = 'original_price';
    public const COLUMN_PLATFORM_DISCOUNT = 'platform_discount';
    public const COLUMN_SELLER_DISCOUNT = 'seller_discount';
    public const COLUMN_TAX_DETAILS = 'tax_details';
    public const COLUMN_TRACKING_DETAILS = 'tracking_details';
    public const COLUMN_CANCEL_REASON = 'cancel_reason';
    public const COLUMN_BUYER_REQUEST_REFUND = 'buyer_request_refund';
    public const COLUMN_BUYER_REQUEST_RETURN = 'buyer_request_return';
    public const COLUMN_REFUND_RETURN_ID = 'refund_return_id';
    public const COLUMN_REFUND_RETURN_STATUS = 'refund_return_status';
    public const COLUMN_SHIPPING_IN_PROGRESS = 'shipping_in_progress';
    public const COLUMN_IS_GIFT = 'is_gift';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ORDER_ITEM,
            self::COLUMN_ID
        );
    }
}
