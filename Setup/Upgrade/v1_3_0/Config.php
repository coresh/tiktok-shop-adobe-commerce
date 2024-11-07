<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_3_0;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m02\AddSimplePropertyInProduct::class,
            \M2E\TikTokShop\Setup\Update\y24_m04\AddVariantSettingsToScheduledAction::class,
            \M2E\TikTokShop\Setup\Update\y24_m04\AddMinMaxPricesToProductTable::class,
            \M2E\TikTokShop\Setup\Update\y24_m04\AddStatusPropertyInVariantSku::class,
            \M2E\TikTokShop\Setup\Update\y24_m04\FixScheduledAction::class,
        ];
    }
}
