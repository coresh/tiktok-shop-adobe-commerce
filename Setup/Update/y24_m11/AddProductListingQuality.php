<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m11;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;

class AddProductListingQuality extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_LISTING_QUALITY_TIER,
            'VARCHAR(20)',
            null,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_LISTING_QUALITY_RECOMMENDATIONS,
            'LONGTEXT',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
