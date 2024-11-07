<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m09;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class RemoveReferencesOfPolicyFromProduct extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier
            ->dropColumn('template_description_mode', true, false)
            ->dropColumn('template_description_id', true, false)
            ->dropColumn('template_selling_format_mode', true, false)
            ->dropColumn('template_selling_format_id', true, false)
            ->dropColumn('template_synchronization_mode', true, false)
            ->dropColumn('template_synchronization_id', true, false);

        $modifier->commit();
    }
}
