<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Upgrade\v1_6_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\TikTokShop\Setup\Update\y24_m05\DropProductBackupTable::class,
        ];
    }
}
