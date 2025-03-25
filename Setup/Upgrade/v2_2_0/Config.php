<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v2_2_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y25_m02\AddNotSalableToSellingPolicy::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\AddIsGiftFlagToProduct::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\AddSampleOrdersAndGiftItems::class,
            \M2E\TikTokShop\Setup\Update\y25_m02\AddIsGiftFlagToUnmanagedProduct::class,
            \M2E\TikTokShop\Setup\Update\y25_m03\FixResponsiblePersonIds::class,
            \M2E\TikTokShop\Setup\Update\y25_m03\AddManufacturerConfiguration::class,
        ];
    }
}
