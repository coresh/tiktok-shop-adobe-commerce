<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup;

class MagentoCoreConfigSettings implements \M2E\Core\Model\Setup\MagentoCoreConfigSettingsInterface
{
    public function getConfigKeyPrefix(): string
    {
        return 'm2e_tts';
    }
}
