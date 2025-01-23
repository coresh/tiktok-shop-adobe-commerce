<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_15_0;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y25_m01\AddShipByDateAndDeliverByDateToOrder::class,
            \M2E\TikTokShop\Setup\Update\y25_m01\AddTrackDirectDatabaseChanges::class,
            \M2E\TikTokShop\Setup\Update\y25_m01\DisableProductCreationForOrders::class,
        ];
    }
}