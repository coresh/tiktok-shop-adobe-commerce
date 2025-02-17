<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v2_0_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y25_m01\ChangeComplianceAndProductTables::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\MigrateLicenseAndRegistrationUserToCore::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\MigrateConfigToCore::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\MigrateRegistryToCore::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\RemoveServerHost::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\RemoveOldCronValues::class,
        ];
    }
}
