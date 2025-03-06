<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class GlobalProduct extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_SOURCE_PRODUCT_ID = 'source_product_id';
    public const COLUMN_GLOBAL_ID = 'global_id';

    public const COLUMN_TITLE = 'title';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_BRAND_ID = 'brand_id';

    public const COLUMN_PACKAGE_DIMENSIONS = 'package_dimensions';
    public const COLUMN_PACKAGE_WEIGHT = 'package_weight';

    public const COLUMN_MAIN_IMAGES = 'main_images';
    public const COLUMN_CERTIFICATIONS = 'certifications';
    public const COLUMN_PRODUCT_ATTRIBUTES = 'product_attributes';
    public const COLUMN_SIZE_CHART = 'size_chart';

    public const COLUMN_MANUFACTURER_IDS = 'manufacturer_ids';
    public const COLUMN_RESPONSIBLE_PERSON_IDS = 'responsible_person_ids';

    public const COLUMN_SOURCE_LOCALE = 'source_locale';

    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_GLOBAL_PRODUCT,
            self::COLUMN_ID
        );
    }
}
