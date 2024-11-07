<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Product\Image;

class Relation extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_PRODUCT_ID = 'listing_product_id';
    public const COLUMN_IMAGE_ID = 'image_id';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_IMAGE_RELATION,
            self::COLUMN_ID
        );
    }
}
