<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m01;

class AddTrackDirectDatabaseChanges extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $config = $this->getConfigModifier();

        $config->delete('/listing/product/inspector/', 'max_allowed_instructions_count');

        $config->insert(
            \M2E\TikTokShop\Helper\Module\Configuration::CONFIG_GROUP,
            'listing_product_inspector_mode',
            '0'
        );
        $config->insert(
            \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config::GROUP,
            \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config::KEY_MAX_ALLOWED_PRODUCT_COUNT,
            '2000'
        );
    }
}
