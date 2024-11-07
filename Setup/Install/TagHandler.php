<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Install;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Tag as TagResource;
use M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation as TagProductRelationResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class TagHandler implements \M2E\TikTokShop\Model\Setup\InstallHandlerInterface
{
    private \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(TablesHelper $tablesHelper)
    {
        $this->tablesHelper = $tablesHelper;
    }

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installTagTable($setup);
        $this->installProductTagRelationTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installTagData($setup);
    }

    private function installTagTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_TAG);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                TagResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                TagResource::COLUMN_ERROR_CODE,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                TagResource::COLUMN_TEXT,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                TagResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addIndex(
                'error_code',
                TagResource::COLUMN_ERROR_CODE,
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE],
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductTagRelationTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_TAG_RELATION);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                TagProductRelationResource::COLUMN_ID,
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
                TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                TagProductRelationResource::COLUMN_TAG_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                TagProductRelationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addIndex(
                'listing_product_id',
                TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID
            )
            ->addIndex('tag_id', TagProductRelationResource::COLUMN_TAG_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installTagData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_TAG);

        $tagCreateDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $tagCreateDate = $tagCreateDate->format('Y-m-d H:i:s');

        $insertData = [
            [
                TagResource::COLUMN_ERROR_CODE => 'has_error',
                TagResource::COLUMN_TEXT => 'Has error',
                TagResource::COLUMN_CREATE_DATE => $tagCreateDate,
            ],
        ];

        $setup->getConnection()->insertMultiple($tableName, $insertData);
    }
}
