<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m09;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddSupportBundleProductsInOrder extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_ITEM);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_COMBINED_LISTING_SKUS,
            'LONGTEXT',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
