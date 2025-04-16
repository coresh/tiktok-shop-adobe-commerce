<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class RemoveIsActiveFlagFromAccount extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ACCOUNT);
        $modifier
            ->dropColumn('is_active', true, false);

        $modifier->commit();
    }
}
