<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\GlobalProduct;

class VariantSku extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_GLOBAL_PRODUCT_ID = 'global_product_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_GLOBAL_ID = 'global_id';

    public const COLUMN_SALES_ATTRIBUTES = 'sales_attributes';
    public const COLUMN_SELLER_SKU = 'seller_sku';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_IDENTIFIER_CODE = 'identifier_code';

    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_GLOBAL_PRODUCT_VARIANT_SKU,
            self::COLUMN_ID
        );
    }
}
