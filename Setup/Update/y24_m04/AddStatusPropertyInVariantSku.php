<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;

class AddStatusPropertyInVariantSku extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createColumn();
        $this->updateVariantStatus();
    }

    private function createColumn(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);
        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_STATUS,
            'SMALLINT UNSIGNED NOT NULL',
            '0',
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_WAREHOUSE_ID,
        );
    }

    private function updateVariantStatus(): void
    {
        $variantTableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);
        $productTableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT);

        $select = $this->getConnection()
            ->select()
            ->join(
                ['p' => $productTableName],
                'p.id = v.product_id',
                ['status' => new \Zend_Db_Expr('IF(p.status IN (0,2), p.status, 8)')]
            );

        $update = $this->getConnection()->updateFromSelect($select, ['v' => $variantTableName]);

        $this->getConnection()->query($update);
    }
}
