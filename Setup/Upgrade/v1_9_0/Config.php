<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_9_0;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m08\AddOnlineIdentifierColumns::class,
            \M2E\TikTokShop\Setup\Update\y24_m08\ChangeTemplateDescriptionTable::class,
        ];
    }
}