<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;

class AddMinMaxPricesToProductTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createMinMaxColumns();
        $this->updateMinMaxData();
        $this->dropOnlinePriceColumn();
    }

    private function createMinMaxColumns()
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            ListingProductResource::COLUMN_ONLINE_MIN_PRICE,
            'DECIMAL(12, 4)',
            null,
            ListingProductResource::COLUMN_ONLINE_QTY,
            false,
            false
        );
        $modifier->addColumn(
            ListingProductResource::COLUMN_ONLINE_MAX_PRICE,
            'DECIMAL(12, 4)',
            null,
            ListingProductResource::COLUMN_ONLINE_MIN_PRICE,
            false,
            false
        );
        $modifier->commit();
    }

    private function dropOnlinePriceColumn(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT);
        $modifier->dropColumn('online_price');
    }

    public function updateMinMaxData(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT);
        if (!$modifier->isColumnExists('online_price')) {
            return;
        }

        $this->getConnection()
             ->update(
                 $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT),
                 [
                     ListingProductResource::COLUMN_ONLINE_MIN_PRICE => new \Zend_Db_Expr('online_price'),
                     ListingProductResource::COLUMN_ONLINE_MAX_PRICE => new \Zend_Db_Expr('online_price'),
                 ]
             );
    }
}
