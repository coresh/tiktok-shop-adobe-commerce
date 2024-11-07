<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_0_4;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m02\AddSellerNameAccountTable::class,
            \M2E\TikTokShop\Setup\Update\y24_m02\AddProductVariantSku::class,
        ];
    }
}
