<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Install;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Config as ConfigResource;
use M2E\TikTokShop\Model\ResourceModel\Lock\Item as LockItemResource;
use M2E\TikTokShop\Model\ResourceModel\Lock\Transactional as LockTransactionalResource;
use M2E\TikTokShop\Model\ResourceModel\OperationHistory as OperationHistoryResource;
use M2E\TikTokShop\Model\ResourceModel\Registry as RegistryResource;
use Magento\Framework\DB\Ddl\Table;

class CoreHandler implements \M2E\TikTokShop\Model\Setup\InstallHandlerInterface
{
    private \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper;
    private \M2E\TikTokShop\Model\Setup\Database\Modifier\ConfigFactory $configFactory;

    public function __construct(
        TablesHelper $tablesHelper,
        \M2E\TikTokShop\Model\Setup\Database\Modifier\ConfigFactory $configFactory
    ) {
        $this->tablesHelper = $tablesHelper;
        $this->configFactory = $configFactory;
    }

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installConfigTable($setup);
        $this->installWizardTable($setup);
        $this->installRegistryTable($setup);
        $this->installOperationHistoryTable($setup);
        $this->installLockItemTable($setup);
        $this->installLockTransactionalTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installConfigData($setup);
        $this->installWizardData($setup);
    }

    private function installConfigTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_CONFIG);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ConfigResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                ConfigResource::COLUMN_GROUP,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ConfigResource::COLUMN_KEY,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ConfigResource::COLUMN_VALUE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ConfigResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ConfigResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('group', ConfigResource::COLUMN_GROUP)
            ->addIndex('key', ConfigResource::COLUMN_KEY)
            ->addIndex('value', ConfigResource::COLUMN_VALUE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installWizardTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_WIZARD);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                'nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'view',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'step',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addIndex('nick', 'nick')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installRegistryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_REGISTRY);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                RegistryResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                RegistryResource::COLUMN_KEY,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                RegistryResource::COLUMN_VALUE,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                RegistryResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                RegistryResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('key', RegistryResource::COLUMN_KEY)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOperationHistoryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_OPERATION_HISTORY);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OperationHistoryResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_START_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_END_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OperationHistoryResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', OperationHistoryResource::COLUMN_NICK)
            ->addIndex('parent_id', OperationHistoryResource::COLUMN_PARENT_ID)
            ->addIndex('initiator', OperationHistoryResource::COLUMN_INITIATOR)
            ->addIndex('start_date', OperationHistoryResource::COLUMN_START_DATE)
            ->addIndex('end_date', OperationHistoryResource::COLUMN_END_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installLockItemTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_LOCK_ITEM);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                LockItemResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                LockItemResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                LockItemResource::COLUMN_PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', LockItemResource::COLUMN_NICK)
            ->addIndex('parent_id', LockItemResource::COLUMN_PARENT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installLockTransactionalTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_LOCK_TRANSACTIONAL);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                LockTransactionalResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                LockTransactionalResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                LockTransactionalResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', LockTransactionalResource::COLUMN_NICK)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installConfigData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $servicingInterval = random_int(43200, 86400);

        $config = $this->configFactory->create(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_CONFIG,
            $setup
        );

        $config->insert('/', 'is_disabled', '0');
        $config->insert('/', 'environment', 'production');
        $config->insert('/license/', 'key');
        $config->insert('/license/domain/', 'real');
        $config->insert('/license/domain/', 'valid');
        $config->insert('/license/domain/', 'is_valid');
        $config->insert('/license/ip/', 'real');
        $config->insert('/license/ip/', 'valid');
        $config->insert('/license/ip/', 'is_valid');
        $config->insert('/license/info/', 'email');
        $config->insert('/server/', 'application_key', '538c3c5ed11d93c8344cfd5219aaa9353a682535');
        $config->insert('/server/', 'host', 'https://api.m2epro.com');
        $config->insert('/cron/', 'mode', '1');
        $config->insert('/cron/', 'runner', 'magento');
        $config->insert('/cron/magento/', 'disabled', '0');
        $config->insert('/cron/task/system/servicing/synchronize/', 'interval', $servicingInterval);
        $config->insert('/logs/clearing/listings/', 'mode', '1');
        $config->insert('/logs/clearing/listings/', 'days', '30');
        $config->insert('/logs/clearing/synchronizations/', 'mode', '1');
        $config->insert('/logs/clearing/synchronizations/', 'days', '30');
        $config->insert('/logs/clearing/orders/', 'mode', '1');
        $config->insert('/logs/clearing/orders/', 'days', '90');
        $config->insert('/logs/listings/', 'last_action_id', '0');
        $config->insert('/logs/grouped/', 'max_records_count', '100000');
        $config->insert('/support/', 'contact_email', 'support@m2epro.com');
        $config->insert('/general/configuration/', 'view_show_block_notices_mode', '1');
        $config->insert('/general/configuration/', 'view_show_products_thumbnails_mode', '1');
        $config->insert('/general/configuration/', 'view_products_grid_use_alternative_mysql_select_mode', '0');
        $config->insert('/general/configuration/', 'other_pay_pal_url', 'paypal.com/cgi-bin/webscr/');
        $config->insert('/general/configuration/', 'product_index_mode', '1');
        $config->insert('/general/configuration/', 'product_force_qty_mode', '0');
        $config->insert('/general/configuration/', 'product_force_qty_value', '10');
        $config->insert('/general/configuration/', 'qty_percentage_rounding_greater', '0');
        $config->insert('/general/configuration/', 'magento_attribute_price_type_converting_mode', '0');
        $config->insert(
            '/general/configuration/',
            'create_with_first_product_options_when_variation_unavailable',
            '1'
        );
        $config->insert('/general/configuration/', 'secure_image_url_in_item_description_mode', '0');
        $config->insert('/magento/product/simple_type/', 'custom_types', '');
        $config->insert('/magento/product/downloadable_type/', 'custom_types', '');
        $config->insert('/magento/product/configurable_type/', 'custom_types', '');
        $config->insert('/magento/product/bundle_type/', 'custom_types', '');
        $config->insert('/magento/product/grouped_type/', 'custom_types', '');
        $config->insert('/health_status/notification/', 'mode', 1);
        $config->insert('/health_status/notification/', 'email', '');
        $config->insert('/health_status/notification/', 'level', 40);
        $config->insert('/listing/product/inspector/', 'max_allowed_instructions_count', '2000');
        $config->insert('/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000');
        $config->insert('/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000');
    }

    private function installWizardData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_WIZARD);

        $insertData = [
            [
                'nick' => 'installationTikTokShop',
                'view' => 'tiktokshop',
                'status' => 0,
                'step' => null,
                'type' => 1,
                'priority' => 2,
            ],
        ];

        $setup->getConnection()->insertMultiple($tableName, $insertData);
    }
}
