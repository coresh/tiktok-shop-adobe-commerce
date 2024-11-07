<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class Action
{
    private const ACTION_NOTHING = 0;

    private int $action;
    private \M2E\TikTokShop\Model\Product $product;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings;

    private function __construct(
        int $action,
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ) {
        $this->product = $product;
        $this->configurator = $configurator;
        $this->variantSettings = $variantSettings;
        $this->action = $action;
    }

    public function getProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->product;
    }

    public function getConfigurator(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    public function getVariantSettings(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings
    {
        return $this->variantSettings;
    }

    public function isActionList(): bool
    {
        return $this->action === \M2E\TikTokShop\Model\Product::ACTION_LIST;
    }

    public function isActionRevise(): bool
    {
        return $this->action === \M2E\TikTokShop\Model\Product::ACTION_REVISE;
    }

    public function isActionStop(): bool
    {
        return $this->action === \M2E\TikTokShop\Model\Product::ACTION_STOP;
    }

    public function isActionRelist(): bool
    {
        return $this->action === \M2E\TikTokShop\Model\Product::ACTION_RELIST;
    }

    public function isActionNothing(): bool
    {
        return $this->action === self::ACTION_NOTHING;
    }

    // ----------------------------------------

    public static function createNothing(\M2E\TikTokShop\Model\Product $product): self
    {
        return new self(
            self::ACTION_NOTHING,
            $product,
            new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator(),
            new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings(),
        );
    }

    public static function createList(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): self {
        return new self(
            \M2E\TikTokShop\Model\Product::ACTION_LIST,
            $product,
            $configurator,
            $variantSettings,
        );
    }

    public static function createRelist(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): self {
        return new self(
            \M2E\TikTokShop\Model\Product::ACTION_RELIST,
            $product,
            $configurator,
            $variantSettings,
        );
    }

    public static function createRevise(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): self {
        return new self(
            \M2E\TikTokShop\Model\Product::ACTION_REVISE,
            $product,
            $configurator,
            $variantSettings,
        );
    }

    public static function createStop(
        \M2E\TikTokShop\Model\Product $product
    ): self {
        return new self(
            \M2E\TikTokShop\Model\Product::ACTION_STOP,
            $product,
            new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator(),
            new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings(),
        );
    }
}
