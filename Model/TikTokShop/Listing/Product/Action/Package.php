<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class Package
{
    private \M2E\TikTokShop\Model\Product $product;
    private Configurator $actionConfigurator;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings */
    private VariantSettings $variantSettings;

    public function __construct(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $actionConfigurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ) {
        $this->product = $product;
        $this->actionConfigurator = $actionConfigurator;
        $this->variantSettings = $variantSettings;
    }

    public function getProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->product;
    }

    public function getActionConfigurator(): Configurator
    {
        return $this->actionConfigurator;
    }

    public function getVariantSettings(): VariantSettings
    {
        return $this->variantSettings;
    }
}
