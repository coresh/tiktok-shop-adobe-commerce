<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class PackageCollection
{
    /** @var Package[] */
    private array $packages = [];

    public function isEmpty(): bool
    {
        return empty($this->packages);
    }

    public function add(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): self {
        $this->packages[$product->getId()] = new Package($product, $configurator, $variantSettings);

        return $this;
    }

    /**
     * @return Package[]
     */
    public function getAll(): array
    {
        return array_values($this->packages);
    }
}
