<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;

class AddVariantSettingsToScheduledAction extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);
        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\ScheduledAction::COLUMN_VARIANTS_SETTINGS,
            'LONGTEXT',
            null,
            \M2E\TikTokShop\Model\ResourceModel\ScheduledAction::COLUMN_TAG,
        );
    }
}
