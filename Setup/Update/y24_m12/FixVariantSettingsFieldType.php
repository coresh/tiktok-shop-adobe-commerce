<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m12;

class FixVariantSettingsFieldType extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION
        );

        $modifier->changeColumn(
            \M2E\TikTokShop\Model\ResourceModel\ScheduledAction::COLUMN_VARIANTS_SETTINGS,
            'LONGTEXT',
            null,
            \M2E\TikTokShop\Model\ResourceModel\ScheduledAction::COLUMN_TAG,
            false
        );

        $modifier->commit();
    }
}
