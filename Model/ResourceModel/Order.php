<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Order extends ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_STORE_ID = 'store_id';
    public const COLUMN_MAGENTO_ORDER_ID = 'magento_order_id';
    public const COLUMN_MAGENTO_ORDER_CREATION_FAILURE = 'magento_order_creation_failure';
    public const COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT = 'magento_order_creation_fails_count';
    public const COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE = 'magento_order_creation_latest_attempt_date';
    public const COLUMN_RESERVATION_STATE = 'reservation_state';
    public const COLUMN_RESERVATION_START_DATE = 'reservation_start_date';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_TTS_ORDER_ID = 'tts_order_id';
    public const COLUMN_ORDER_STATUS = 'order_status';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_PURCHASE_CREATE_DATE = 'purchase_create_date';
    public const COLUMN_PURCHASE_UPDATE_DATE = 'purchase_update_date';
    public const COLUMN_PAID_AMOUNT = 'paid_amount';
    public const COLUMN_CURRENCY = 'currency';
    public const COLUMN_TAX_DETAILS = 'tax_details';
    public const COLUMN_BUYER_USER_ID = 'buyer_user_id';
    public const COLUMN_BUYER_NAME = 'buyer_name';
    public const COLUMN_BUYER_EMAIL = 'buyer_email';
    public const COLUMN_BUYER_MESSAGE = 'buyer_message';
    public const COLUMN_PAYMENT_METHOD_NAME = 'payment_method_name';
    public const COLUMN_PAYMENT_DATE = 'payment_date';
    public const COLUMN_PAYMENT_DETAILS = 'payment_details';
    public const COLUMN_SHIPPING_DETAILS = 'shipping_details';
    public const COLUMN_SHIP_BY_DATE = 'ship_by_date';
    public const COLUMN_DELIVER_BY_DATE = 'deliver_by_date';
    public const COLUMN_BUYER_CANCELLATION_REQUEST = 'buyer_cancellation_request';
    public const COLUMN_BUYER_CANCELLATION_REQUEST_REASON = 'buyer_cancellation_request_reason';
    public const COLUMN_IS_SAMPLE = 'is_sample';
    public const COLUMN_CPF = 'cpf';
    public const COLUMN_CPF_NAME = 'cpf_name';
    public const COLUMN_CREATE_DATE = 'create_date';
    public const COLUMN_UPDATE_DATE = 'update_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ORDER,
            self::COLUMN_ID
        );
    }

    public function loadByMagentoOrderId(\M2E\TikTokShop\Model\Order $object, int $magentoOrderId): Order
    {
        return $this->load($object, $magentoOrderId, 'magento_order_id');
    }
}
