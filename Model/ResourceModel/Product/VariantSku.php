<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Product;

class VariantSku extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_SKU_ID = 'sku_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_ONLINE_SKU = 'online_sku';
    public const COLUMN_ONLINE_PRICE = 'online_price';
    public const COLUMN_ONLINE_QTY = 'online_qty';
    public const COLUMN_ONLINE_IMAGE = 'online_image';
    public const COLUMN_ONLINE_IDENTIFIER_TYPE = 'online_identifier_type';
    public const COLUMN_ONLINE_IDENTIFIER_ID = 'online_identifier_id';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_VARIANT_SKU,
            self::COLUMN_ID
        );
    }
}
