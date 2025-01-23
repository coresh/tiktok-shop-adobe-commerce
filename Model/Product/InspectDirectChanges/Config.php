<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\InspectDirectChanges;

class Config
{
    public const GROUP = '/listing/product/inspector/';
    public const KEY_MAX_ALLOWED_PRODUCT_COUNT = 'max_allowed_products_count';

    private \M2E\TikTokShop\Model\Config\Manager $config;
    private \M2E\TikTokShop\Helper\Module\Configuration $configurationHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Helper\Module\Configuration $configurationHelper
    ) {
        $this->configurationHelper = $configurationHelper;
        $this->config = $config;
    }

    public function isEnableProductInspectorMode(): bool
    {
        return (bool)$this->configurationHelper->getProductInspectorMode();
    }

    public function getMaxAllowedProducts(): int
    {
        return (int)$this->config->getGroupValue(
            self::GROUP,
            self::KEY_MAX_ALLOWED_PRODUCT_COUNT
        );
    }
}
