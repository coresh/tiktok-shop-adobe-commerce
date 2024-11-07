<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template;

class Synchronization extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_IS_CUSTOM_TEMPLATE = 'is_custom_template';
    public const COLUMN_LIST_MODE = 'list_mode';
    public const COLUMN_LIST_STATUS_ENABLED = 'list_status_enabled';
    public const COLUMN_LIST_IS_IN_STOCK = 'list_is_in_stock';
    public const COLUMN_LIST_QTY_CALCULATED = 'list_qty_calculated';
    public const COLUMN_LIST_QTY_CALCULATED_VALUE = 'list_qty_calculated_value';
    public const COLUMN_LIST_ADVANCED_RULES_MODE = 'list_advanced_rules_mode';
    public const COLUMN_LIST_ADVANCED_RULES_FILTERS = 'list_advanced_rules_filters';
    public const COLUMN_REVISE_UPDATE_QTY = 'revise_update_qty';
    public const COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_MODE = 'revise_update_qty_max_applied_value_mode';
    public const COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE = 'revise_update_qty_max_applied_value';
    public const COLUMN_REVISE_UPDATE_PRICE = 'revise_update_price';
    public const COLUMN_REVISE_UPDATE_TITLE = 'revise_update_title';
    public const COLUMN_REVISE_UPDATE_CATEGORIES = 'revise_update_categories';
    public const COLUMN_REVISE_UPDATE_IMAGES = 'revise_update_images';
    public const COLUMN_REVISE_UPDATE_DESCRIPTION = 'revise_update_description';
    public const COLUMN_REVISE_UPDATE_OTHER = 'revise_update_other';
    public const COLUMN_RELIST_MODE = 'relist_mode';
    public const COLUMN_RELIST_FILTER_USER_LOCK = 'relist_filter_user_lock';
    public const COLUMN_RELIST_STATUS_ENABLED = 'relist_status_enabled';
    public const COLUMN_RELIST_IS_IN_STOCK = 'relist_is_in_stock';
    public const COLUMN_RELIST_QTY_CALCULATED = 'relist_qty_calculated';
    public const COLUMN_RELIST_QTY_CALCULATED_VALUE = 'relist_qty_calculated_value';
    public const COLUMN_RELIST_ADVANCED_RULES_MODE = 'relist_advanced_rules_mode';
    public const COLUMN_RELIST_ADVANCED_RULES_FILTERS = 'relist_advanced_rules_filters';
    public const COLUMN_STOP_MODE = 'stop_mode';
    public const COLUMN_STOP_STATUS_DISABLED = 'stop_status_disabled';
    public const COLUMN_STOP_OUT_OFF_STOCK = 'stop_out_off_stock';
    public const COLUMN_STOP_QTY_CALCULATED = 'stop_qty_calculated';
    public const COLUMN_STOP_QTY_CALCULATED_VALUE = 'stop_qty_calculated_value';
    public const COLUMN_STOP_ADVANCED_RULES_MODE = 'stop_advanced_rules_mode';
    public const COLUMN_STOP_ADVANCED_RULES_FILTERS = 'stop_advanced_rules_filters';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_SYNCHRONIZATION,
            self::COLUMN_ID,
        );
    }
}
