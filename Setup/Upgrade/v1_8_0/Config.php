<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_8_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
            \M2E\TikTokShop\Setup\Update\y24_m06\AddIsSkippedColumnToListingWizard::class,
            \M2E\TikTokShop\Setup\Update\y24_m07\AddBuyerReturnRefundColumnsToOrderItem::class,
            \M2E\TikTokShop\Setup\Update\y24_m07\AddBuyerCancellationColumnsToOrder::class,
        ];
    }
}
