<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Category;

class Attribute extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_DICTIONARY_ID = 'category_dictionary_id';
    public const COLUMN_ATTRIBUTE_ID = 'attribute_id';
    public const COLUMN_ATTRIBUTE_NAME = 'attribute_name';
    public const COLUMN_ATTRIBUTE_TYPE = 'attribute_type';
    public const COLUMN_VALUE_MODE = 'value_mode';
    public const COLUMN_VALUE_RECOMMENDED = 'value_recommended';
    public const COLUMN_VALUE_CUSTOM_VALUE = 'value_custom_value';
    public const COLUMN_VALUE_CUSTOM_ATTRIBUTE = 'value_custom_attribute';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_CATEGORY_ATTRIBUTES,
            self::COLUMN_ID
        );
    }
}
