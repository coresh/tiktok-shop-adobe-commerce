<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m08;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddOnlineIdentifierColumns extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->addIdentifierToVariantSku();
        $this->addIdentifierToListingOther();
    }

    private function addIdentifierToVariantSku()
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT_VARIANT_SKU);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_ONLINE_IDENTIFIER_ID,
            'VARCHAR(50)',
            null,
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_ONLINE_IMAGE,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_ONLINE_IDENTIFIER_TYPE,
            'VARCHAR(10)',
            null,
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_ONLINE_IDENTIFIER_ID,
            false,
            false
        );

        $modifier->commit();
    }

    private function addIdentifierToListingOther()
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Listing\Other::COLUMN_IDENTIFIER_ID,
            'VARCHAR(50)',
            null,
            \M2E\TikTokShop\Model\ResourceModel\Listing\Other::COLUMN_CATEGORIES_DATA,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Listing\Other::COLUMN_IDENTIFIER_TYPE,
            'VARCHAR(10)',
            null,
            \M2E\TikTokShop\Model\ResourceModel\Listing\Other::COLUMN_IDENTIFIER_ID,
            false,
            false
        );

        $modifier->commit();
    }
}
