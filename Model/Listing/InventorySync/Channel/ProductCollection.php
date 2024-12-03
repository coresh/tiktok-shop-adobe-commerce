<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel;

class ProductCollection
{
    /** @var Product[] */
    private array $products = [];

    public function empty(): bool
    {
        return empty($this->products);
    }

    public function has(string $productId): bool
    {
        return isset($this->products[$productId]);
    }

    public function add(Product $product): void
    {
        $this->products[$product->getProductId()] = $product;
    }

    public function get(string $productId): Product
    {
        return $this->products[$productId];
    }

    public function remove(string $productId): void
    {
        unset($this->products[$productId]);
    }

    /**
     * @return Product[]
     */
    public function getAll(): array
    {
        return array_values($this->products);
    }

    public function getProductsIds(): array
    {
        return array_keys($this->products);
    }
}
