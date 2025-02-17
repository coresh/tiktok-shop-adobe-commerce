<?php

namespace M2E\TikTokShop\Helper\Module\Database;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

class Tables
{
    public const PREFIX = 'm2e_tts_';

    public const TABLE_NAME_WIZARD = self::PREFIX . 'wizard';

    public const TABLE_NAME_ACCOUNT = self::PREFIX . 'account';
    public const TABLE_NAME_SHOP = self::PREFIX . 'shop';
    public const TABLE_NAME_WAREHOUSE = self::PREFIX . 'warehouse';
    public const TABLE_NAME_SHIPPING_PROVIDERS = self::PREFIX . 'shipping_providers';

    public const TABLE_NAME_LISTING = self::PREFIX . 'listing';
    public const TABLE_NAME_LISTING_LOG = self::PREFIX . 'listing_log';
    public const TABLE_NAME_LISTING_WIZARD = self::PREFIX . 'listing_wizard';
    public const TABLE_NAME_LISTING_WIZARD_STEP = self::PREFIX . 'listing_wizard_step';
    public const TABLE_NAME_LISTING_WIZARD_PRODUCT = self::PREFIX . 'listing_wizard_product';

    public const TABLE_NAME_PRODUCT = self::PREFIX . 'product';
    public const TABLE_NAME_PRODUCT_VARIANT_SKU = self::PREFIX . 'product_variant_sku';
    public const TABLE_NAME_PRODUCT_INSTRUCTION = self::PREFIX . 'product_instruction';
    public const TABLE_NAME_PRODUCT_SCHEDULED_ACTION = self::PREFIX . 'product_scheduled_action';

    public const TABLE_NAME_LOCK_ITEM = self::PREFIX . 'lock_item';
    public const TABLE_NAME_LOCK_TRANSACTIONAL = self::PREFIX . 'lock_transactional';

    public const TABLE_NAME_PROCESSING = self::PREFIX . 'processing';
    public const TABLE_NAME_PROCESSING_PARTIAL_DATA = self::PREFIX . 'processing_partial_data';
    public const TABLE_NAME_PROCESSING_LOCK = self::PREFIX . 'processing_lock';

    public const TABLE_NAME_STOP_QUEUE = self::PREFIX . 'stop_queue';

    public const TABLE_NAME_SYNCHRONIZATION_LOG = self::PREFIX . 'synchronization_log';
    public const TABLE_NAME_SYSTEM_LOG = self::PREFIX . 'system_log';
    public const TABLE_NAME_OPERATION_HISTORY = self::PREFIX . 'operation_history';

    public const TABLE_NAME_TEMPLATE_SELLING_FORMAT = self::PREFIX . 'template_selling_format';
    public const TABLE_NAME_TEMPLATE_SYNCHRONIZATION = self::PREFIX . 'template_synchronization';
    public const TABLE_NAME_TEMPLATE_DESCRIPTION = self::PREFIX . 'template_description';
    public const TABLE_NAME_TEMPLATE_COMPLIANCE = self::PREFIX . 'template_compliance';

    public const TABLE_NAME_TAG = self::PREFIX . 'tag';
    public const TABLE_NAME_PRODUCT_TAG_RELATION = self::PREFIX . 'product_tag_relation';

    public const TABLE_NAME_CATEGORY_TREE = self::PREFIX . 'category_tree';
    public const TABLE_NAME_CATEGORY_DICTIONARY = self::PREFIX . 'category_dictionary';
    public const TABLE_NAME_TEMPLATE_CATEGORY_ATTRIBUTES = self::PREFIX . 'template_category_attributes';

    public const TABLE_NAME_IMAGE = self::PREFIX . 'image';
    public const TABLE_NAME_PRODUCT_IMAGE_RELATION = self::PREFIX . 'product_image_relation';

    public const TABLE_NAME_ORDER = self::PREFIX . 'order';
    public const TABLE_NAME_ORDER_ITEM = self::PREFIX . 'order_item';
    public const TABLE_NAME_ORDER_LOG = self::PREFIX . 'order_log';
    public const TABLE_NAME_ORDER_NOTE = self::PREFIX . 'order_note';
    public const TABLE_NAME_ORDER_CHANGE = self::PREFIX . 'order_change';

    public const TABLE_NAME_UNMANAGED_PRODUCT = self::PREFIX . 'unmanaged_product';
    public const TABLE_NAME_UNMANAGED_PRODUCT_VARIANT_SKU = self::PREFIX . 'unmanaged_product_variant_sku';

    public const TABLE_NAME_PROMOTION = self::PREFIX . 'promotion';
    public const TABLE_NAME_PROMOTION_PRODUCT = self::PREFIX . 'promotion_product';

    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private Structure $databaseHelper;
    private \M2E\TikTokShop\Helper\Magento\Staging $stagingHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\TikTokShop\Helper\Module\Database\Structure $databaseHelper,
        \M2E\TikTokShop\Helper\Magento\Staging $stagingHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->databaseHelper = $databaseHelper;
        $this->stagingHelper = $stagingHelper;
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function isExists(string $tableName): bool
    {
        return $this->resourceConnection
            ->getConnection()
            ->isTableExists($this->getFullName($tableName));
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getFullName(string $tableName): string
    {
        if (strpos($tableName, self::PREFIX) === false) {
            $tableName = self::PREFIX . $tableName;
        }

        return $this->databaseHelper->getTableNameWithPrefix($tableName);
    }

    /**
     * @param string $oldTable
     * @param string $newTable
     *
     * @return bool
     */
    public function renameTable(string $oldTable, string $newTable): bool
    {
        $oldTable = $this->getFullName($oldTable);
        $newTable = $this->getFullName($newTable);

        if (
            $this->resourceConnection->getConnection()->isTableExists($oldTable) &&
            !$this->resourceConnection->getConnection()->isTableExists($newTable)
        ) {
            $this->resourceConnection->getConnection()->renameTable(
                $oldTable,
                $newTable,
            );

            return true;
        }

        return false;
    }

    /**
     * @param array|string $table
     * @param string $columnName
     *
     * @return string
     */
    public function normalizeEavColumn($table, string $columnName): string
    {
        if (
            $this->stagingHelper->isInstalled() &&
            $this->stagingHelper->isStagedTable($table, ProductAttributeInterface::ENTITY_TYPE_CODE) &&
            strpos($columnName, 'entity_id') !== false
        ) {
            $columnName = str_replace(
                'entity_id',
                $this->stagingHelper->getTableLinkField(ProductAttributeInterface::ENTITY_TYPE_CODE),
                $columnName,
            );
        }

        return $columnName;
    }

    // ----------------------------------------

    /**
     * @return string[]
     */
    public static function getAllTables(): array
    {
        return array_keys(self::getTablesResourcesModels());
    }

    public static function getTableModel(string $tableName): string
    {
        $tablesModels = self::getTablesModels();

        return $tablesModels[$tableName];
    }

    public static function getTableResourceModel(string $tableName): string
    {
        $tablesModels = self::getTablesResourcesModels();

        return $tablesModels[$tableName];
    }

    private static function getTablesResourcesModels(): array
    {
        return [
            self::TABLE_NAME_WIZARD => \M2E\TikTokShop\Model\ResourceModel\Wizard::class,
            self::TABLE_NAME_ACCOUNT => \M2E\TikTokShop\Model\ResourceModel\Account::class,
            self::TABLE_NAME_SHOP => \M2E\TikTokShop\Model\ResourceModel\Shop::class,
            self::TABLE_NAME_WAREHOUSE => \M2E\TikTokShop\Model\ResourceModel\Warehouse::class,
            self::TABLE_NAME_SHIPPING_PROVIDERS => \M2E\TikTokShop\Model\ResourceModel\ShippingProvider::class,
            self::TABLE_NAME_LISTING => \M2E\TikTokShop\Model\ResourceModel\Listing::class,
            self::TABLE_NAME_LISTING_LOG => \M2E\TikTokShop\Model\ResourceModel\Listing\Log::class,
            self::TABLE_NAME_LISTING_WIZARD => \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::class,
            self::TABLE_NAME_LISTING_WIZARD_STEP => \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::class,
            self::TABLE_NAME_LISTING_WIZARD_PRODUCT => \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Product::class,
            self::TABLE_NAME_PRODUCT => \M2E\TikTokShop\Model\ResourceModel\Product::class,
            self::TABLE_NAME_PRODUCT_VARIANT_SKU => \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::class,
            self::TABLE_NAME_PRODUCT_INSTRUCTION => \M2E\TikTokShop\Model\ResourceModel\Instruction::class,
            self::TABLE_NAME_PRODUCT_SCHEDULED_ACTION => \M2E\TikTokShop\Model\ResourceModel\ScheduledAction::class,
            self::TABLE_NAME_LOCK_ITEM => \M2E\TikTokShop\Model\ResourceModel\Lock\Item::class,
            self::TABLE_NAME_LOCK_TRANSACTIONAL => \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::class,
            self::TABLE_NAME_PROCESSING => \M2E\TikTokShop\Model\ResourceModel\Processing::class,
            self::TABLE_NAME_PROCESSING_PARTIAL_DATA => \M2E\TikTokShop\Model\ResourceModel\Processing\PartialData::class,
            self::TABLE_NAME_PROCESSING_LOCK => \M2E\TikTokShop\Model\ResourceModel\Processing\Lock::class,
            self::TABLE_NAME_STOP_QUEUE => \M2E\TikTokShop\Model\ResourceModel\StopQueue::class,
            self::TABLE_NAME_SYNCHRONIZATION_LOG => \M2E\TikTokShop\Model\ResourceModel\Synchronization\Log::class,
            self::TABLE_NAME_SYSTEM_LOG => \M2E\TikTokShop\Model\ResourceModel\Log\System::class,
            self::TABLE_NAME_OPERATION_HISTORY => \M2E\TikTokShop\Model\ResourceModel\OperationHistory::class,
            self::TABLE_NAME_TEMPLATE_SELLING_FORMAT => \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::class,
            self::TABLE_NAME_TEMPLATE_SYNCHRONIZATION => \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization::class,
            self::TABLE_NAME_TEMPLATE_DESCRIPTION => \M2E\TikTokShop\Model\ResourceModel\Template\Description::class,
            self::TABLE_NAME_TEMPLATE_COMPLIANCE => \M2E\TikTokShop\Model\ResourceModel\Template\Compliance::class,
            self::TABLE_NAME_TAG => \M2E\TikTokShop\Model\ResourceModel\Tag::class,
            self::TABLE_NAME_PRODUCT_TAG_RELATION => \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation::class,
            self::TABLE_NAME_CATEGORY_TREE => \M2E\TikTokShop\Model\ResourceModel\Category\Tree::class,
            self::TABLE_NAME_CATEGORY_DICTIONARY => \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::class,
            self::TABLE_NAME_TEMPLATE_CATEGORY_ATTRIBUTES => \M2E\TikTokShop\Model\ResourceModel\Category\Attribute::class,
            self::TABLE_NAME_IMAGE => \M2E\TikTokShop\Model\ResourceModel\Image::class,
            self::TABLE_NAME_PRODUCT_IMAGE_RELATION => \M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation::class,
            self::TABLE_NAME_ORDER => \M2E\TikTokShop\Model\ResourceModel\Order::class,
            self::TABLE_NAME_ORDER_ITEM => \M2E\TikTokShop\Model\ResourceModel\Order\Item::class,
            self::TABLE_NAME_ORDER_LOG => \M2E\TikTokShop\Model\ResourceModel\Order\Log::class,
            self::TABLE_NAME_ORDER_NOTE => \M2E\TikTokShop\Model\ResourceModel\Order\Note::class,
            self::TABLE_NAME_ORDER_CHANGE => \M2E\TikTokShop\Model\ResourceModel\Order\Change::class,
            self::TABLE_NAME_UNMANAGED_PRODUCT => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::class,
            self::TABLE_NAME_UNMANAGED_PRODUCT_VARIANT_SKU => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku::class,
            self::TABLE_NAME_PROMOTION => \M2E\TikTokShop\Model\ResourceModel\Promotion::class,
            self::TABLE_NAME_PROMOTION_PRODUCT => \M2E\TikTokShop\Model\ResourceModel\Promotion\Product::class,
        ];
    }

    public static function getTablesModels(): array
    {
        return [
            self::TABLE_NAME_WIZARD => \M2E\TikTokShop\Model\Wizard::class,
            self::TABLE_NAME_ACCOUNT => \M2E\TikTokShop\Model\Account::class,
            self::TABLE_NAME_SHOP => \M2E\TikTokShop\Model\Shop::class,
            self::TABLE_NAME_WAREHOUSE => \M2E\TikTokShop\Model\Warehouse::class,
            self::TABLE_NAME_SHIPPING_PROVIDERS => \M2E\TikTokShop\Model\ShippingProvider::class,
            self::TABLE_NAME_LISTING => \M2E\TikTokShop\Model\Listing::class,
            self::TABLE_NAME_LISTING_LOG => \M2E\TikTokShop\Model\Listing\Log::class,
            self::TABLE_NAME_LISTING_WIZARD => \M2E\TikTokShop\Model\Listing\Wizard::class,
            self::TABLE_NAME_LISTING_WIZARD_STEP => \M2E\TikTokShop\Model\Listing\Wizard\Step::class,
            self::TABLE_NAME_LISTING_WIZARD_PRODUCT => \M2E\TikTokShop\Model\Listing\Wizard\Product::class,
            self::TABLE_NAME_PRODUCT => \M2E\TikTokShop\Model\Product::class,
            self::TABLE_NAME_PRODUCT_VARIANT_SKU => \M2E\TikTokShop\Model\Product\VariantSku::class,
            self::TABLE_NAME_PRODUCT_INSTRUCTION => \M2E\TikTokShop\Model\Instruction::class,
            self::TABLE_NAME_PRODUCT_SCHEDULED_ACTION => \M2E\TikTokShop\Model\ScheduledAction::class,
            self::TABLE_NAME_LOCK_ITEM => \M2E\TikTokShop\Model\Lock\Item::class,
            self::TABLE_NAME_LOCK_TRANSACTIONAL => \M2E\TikTokShop\Model\Lock\Transactional::class,
            self::TABLE_NAME_PROCESSING => \M2E\TikTokShop\Model\Processing::class,
            self::TABLE_NAME_PROCESSING_PARTIAL_DATA => \M2E\TikTokShop\Model\Processing\PartialData::class,
            self::TABLE_NAME_PROCESSING_LOCK => \M2E\TikTokShop\Model\Processing\Lock::class,
            self::TABLE_NAME_STOP_QUEUE => \M2E\TikTokShop\Model\StopQueue::class,
            self::TABLE_NAME_SYNCHRONIZATION_LOG => \M2E\TikTokShop\Model\Synchronization\Log::class,
            self::TABLE_NAME_SYSTEM_LOG => \M2E\TikTokShop\Model\Log\System::class,
            self::TABLE_NAME_OPERATION_HISTORY => \M2E\TikTokShop\Model\OperationHistory::class,
            self::TABLE_NAME_TEMPLATE_SELLING_FORMAT => \M2E\TikTokShop\Model\Template\SellingFormat::class,
            self::TABLE_NAME_TEMPLATE_SYNCHRONIZATION => \M2E\TikTokShop\Model\Template\Synchronization::class,
            self::TABLE_NAME_TEMPLATE_DESCRIPTION => \M2E\TikTokShop\Model\Template\Description::class,
            self::TABLE_NAME_TEMPLATE_COMPLIANCE => \M2E\TikTokShop\Model\Template\Compliance::class,
            self::TABLE_NAME_TAG => \M2E\TikTokShop\Model\Tag\Entity::class,
            self::TABLE_NAME_PRODUCT_TAG_RELATION => \M2E\TikTokShop\Model\Tag\ListingProduct\Relation::class,
            self::TABLE_NAME_CATEGORY_TREE => \M2E\TikTokShop\Model\Category\Tree::class,
            self::TABLE_NAME_CATEGORY_DICTIONARY => \M2E\TikTokShop\Model\Category\Dictionary::class,
            self::TABLE_NAME_TEMPLATE_CATEGORY_ATTRIBUTES => \M2E\TikTokShop\Model\Category\CategoryAttribute::class,
            self::TABLE_NAME_IMAGE => \M2E\TikTokShop\Model\Image::class,
            self::TABLE_NAME_PRODUCT_IMAGE_RELATION => \M2E\TikTokShop\Model\Product\Image\Relation::class,
            self::TABLE_NAME_ORDER => \M2E\TikTokShop\Model\Order::class,
            self::TABLE_NAME_ORDER_ITEM => \M2E\TikTokShop\Model\Order\Item::class,
            self::TABLE_NAME_ORDER_LOG => \M2E\TikTokShop\Model\Order\Log::class,
            self::TABLE_NAME_ORDER_NOTE => \M2E\TikTokShop\Model\Order\Note::class,
            self::TABLE_NAME_ORDER_CHANGE => \M2E\TikTokShop\Model\Order\Change::class,
            self::TABLE_NAME_UNMANAGED_PRODUCT => \M2E\TikTokShop\Model\UnmanagedProduct::class,
            self::TABLE_NAME_UNMANAGED_PRODUCT_VARIANT_SKU => \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku::class,
            self::TABLE_NAME_PROMOTION => \M2E\TikTokShop\Model\Promotion::class,
            self::TABLE_NAME_PROMOTION_PRODUCT => \M2E\TikTokShop\Model\Promotion\Product::class,
        ];
    }

    public static function isModuleTable(string $tableName): bool
    {
        return strpos($tableName, self::PREFIX) !== false;
    }
}
