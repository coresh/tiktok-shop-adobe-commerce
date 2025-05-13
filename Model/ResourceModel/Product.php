<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class Product extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_TTS_PRODUCT_ID = 'tts_product_id';
    public const COLUMN_IS_SIMPLE = 'is_simple';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_GLOBAL_PRODUCT_ID = 'global_product_id';
    public const COLUMN_STATUS_CHANGER = 'status_changer';
    public const COLUMN_ONLINE_TITLE = 'online_title';
    public const COLUMN_ONLINE_DESCRIPTION = 'online_description';
    public const COLUMN_ONLINE_BRAND_ID = 'online_brand_id';
    public const COLUMN_ONLINE_BRAND_NAME = 'online_brand_name';
    public const COLUMN_ONLINE_CATEGORY = 'online_category';
    public const COLUMN_ONLINE_CATEGORIES_DATA = 'online_categories_data';
    public const COLUMN_ONLINE_QTY = 'online_qty';
    public const COLUMN_IS_GIFT = 'is_gift';
    public const COLUMN_ONLINE_MIN_PRICE = 'online_min_price';
    public const COLUMN_ONLINE_MAX_PRICE = 'online_max_price';
    public const COLUMN_ONLINE_MANUFACTURER_ID = 'online_manufacturer_id';
    public const COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS = 'online_responsible_person_ids';
    public const COLUMN_TEMPLATE_CATEGORY_ID = 'template_category_id';
    public const COLUMN_LAST_BLOCKING_ERROR_DATE = 'last_blocking_error_date';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_LISTING_QUALITY_TIER = 'listing_quality_tier';
    public const COLUMN_LISTING_QUALITY_RECOMMENDATIONS = 'listing_quality_recommendations';
    public const COLUMN_AUDIT_FAILED_REASONS = 'audit_failed_reasons';
    public const COLUMN_MANUFACTURER_CONFIG_ID = 'manufacturer_config_id';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT,
            self::COLUMN_ID
        );
    }

    public function getProductIds(array $listingProductIds): array
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['lp' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns(['product_id'])
                       ->where('id IN (?)', $listingProductIds);

        return $select->query()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getTemplateCategoryIds(array $listingProductIds, $columnName, $returnNull = false)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['product' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns([$columnName])
                       ->where('id IN (?)', $listingProductIds);

        !$returnNull && $select->where("{$columnName} IS NOT NULL");

        foreach ($select->query()->fetchAll() as $row) {
            $id = $row[$columnName] !== null ? (int)$row[$columnName] : null;
            if (!$returnNull) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }
}
