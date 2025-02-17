<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m06;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class RemoveListingProductConfigurations extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $config = $this->getConfigModifier(\M2E\TikTokShop\Helper\Module::IDENTIFIER);

        $config->delete('/cron/task/listing/product/process_instructions/', 'mode');
    }
}
