<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;
use M2E\TikTokShop\Model\ResourceModel\Product\VariantSku as ListingProductVariantResource;

class WarehouseUpdates extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->modifyListingTable();
        $this->addColumnToVariantSkuTable();
        $this->updateListingWarehouse();
        $this->removeColumnFromVariantSkuTable();
    }

    private function modifyListingTable(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_LISTING);
        $modifier->addColumn(
            ListingResource::COLUMN_WAREHOUSE_ID,
            'INT UNSIGNED NOT NULL',
            null,
            ListingResource::COLUMN_SHOP_ID,
            false,
            false
        );

        $modifier->commit();
    }

    private function addColumnToVariantSkuTable(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);
        $modifier
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_WAREHOUSE_ID,
                'VARCHAR(255)',
                null,
                ListingProductVariantResource::COLUMN_STATUS,
                false,
                false
            );

        $modifier->commit();
    }

    private function removeColumnFromVariantSkuTable(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);
        $modifier->dropColumn('warehouse_id');
        $modifier->commit();
    }

    private function updateListingWarehouse(): void
    {
        $connection = $this->getConnection();
        $listingTable = $this->getFullTableName(TablesHelper::TABLE_NAME_LISTING);
        $select = $connection->select()->from($listingTable);

        $stmt = $connection->query($select);
        while ($row = $stmt->fetch()) {
            $listingId = (int)$row['id'];
            $warehouseId = $this->getWarehouseId($listingId, (int)$row['shop_id']);
            if ($warehouseId) {
                $connection->update(
                    $listingTable,
                    ['warehouse_id' => $warehouseId],
                    ['id = ?' => $listingId]
                );
            }
        }
    }

    private function getWarehouseId(int $listingId, int $shopId): ?int
    {
        $warehouseId = $this->getWarehouseIdFromProductVariant($listingId);
        if (!$warehouseId) {
            $warehouseId = $this->getWarehouseIdFromShop($shopId, true);
        }
        if (!$warehouseId) {
            $warehouseId = $this->getWarehouseIdFromShop($shopId);
        }

        return $warehouseId;
    }

    private function getWarehouseIdFromProductVariant(int $listingId): ?int
    {
        $connection = $this->getConnection();
        $productTable = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT);
        $variantTable = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);
        $variantSelect = $connection->select()
                                    ->from(['v' => $variantTable], ['warehouse_id'])
                                    ->join(
                                        ['p' => $productTable],
                                        'p.id = v.product_id',
                                        []
                                    )
                                    ->where('p.listing_id = ?', $listingId)
                                    ->where('v.warehouse_id IS NOT NULL')
                                    ->order('v.id ASC')
                                    ->limit(1);
        $warehouseId = $connection->fetchOne($variantSelect);

        return $warehouseId ? (int)$warehouseId : null;
    }

    private function getWarehouseIdFromShop(int $shopId, bool $isDefault = false): ?int
    {
        $connection = $this->getConnection();
        $warehouseTable = $this->getFullTableName(TablesHelper::TABLE_NAME_WAREHOUSE);
        $warehouseSelect = $connection->select()
                                      ->from(['w' => $warehouseTable], ['id'])
                                      ->where('w.shop_id = ?', $shopId)
                                      ->limit(1);
        if ($isDefault) {
            $warehouseSelect->where('w.is_default = 1');
        }

        $warehouseId = $connection->fetchOne($warehouseSelect);

        return $warehouseId ? (int)$warehouseId : null;
    }
}
