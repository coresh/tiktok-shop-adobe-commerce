<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;

class AddIsGiftFlagToUnmanagedProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_UNMANAGED_PRODUCT);
        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_IS_GIFT,
            'SMALLINT UNSIGNED NOT NULL',
            \M2E\TikTokShop\Model\Product::IS_GIFT_OFF,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
