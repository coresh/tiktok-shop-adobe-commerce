<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_11_0;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m10\AddUnmanagedProductVariant::class,
            \M2E\TikTokShop\Setup\Update\y24_m10\AddPromotion::class,
        ];
    }
}