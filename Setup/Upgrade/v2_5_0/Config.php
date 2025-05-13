<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v2_5_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y25_m03\AddAuditFailedReasonsColumnToProduct::class,
            \M2E\TikTokShop\Setup\Update\y25_m04\WarehouseUpdates::class,
        ];
    }
}
