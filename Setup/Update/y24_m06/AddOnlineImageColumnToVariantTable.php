<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m06;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddOnlineImageColumnToVariantTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT_VARIANT_SKU);
        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_ONLINE_IMAGE,
            'VARCHAR(255)',
            null,
            \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku::COLUMN_ONLINE_QTY,
        );
    }
}
