<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\AffectedProduct;

class Collection
{
    /** @var \M2E\TikTokShop\Model\Product\AffectedProduct\Product[] */
    private array $results = [];

    public function addResult(Product $result): void
    {
        $this->results[] = $result;
    }

    public function isEmpty(): bool
    {
        return $this->results === [];
    }

    /**
     * @return \M2E\TikTokShop\Model\Product\AffectedProduct\Product[]
     */
    public function getProducts(): array
    {
        return $this->results;
    }
}
