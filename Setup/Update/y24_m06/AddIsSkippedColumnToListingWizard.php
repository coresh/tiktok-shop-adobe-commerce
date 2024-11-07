<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m06;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step as StepResource;

class AddIsSkippedColumnToListingWizard extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_WIZARD_STEP);
        $modifier->addColumn(
            StepResource::COLUMN_IS_SKIPPED,
            'SMALLINT UNSIGNED NOT NULL',
            '0',
            StepResource::COLUMN_IS_COMPLETED,
            true
        );
    }
}
