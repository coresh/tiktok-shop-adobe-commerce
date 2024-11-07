<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_7_0;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m06\ChangeUniqueConstrainInImageTable::class,
            \M2E\TikTokShop\Setup\Update\y24_m06\AddOnlineImageColumnToVariantTable::class,
            \M2E\TikTokShop\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
            \M2E\TikTokShop\Setup\Update\y24_m06\ListingWizard::class,
        ];
    }
}