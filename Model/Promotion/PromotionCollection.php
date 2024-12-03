<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

class PromotionCollection
{
    /** @var \M2E\TikTokShop\Model\Promotion[] */
    private array $promotions = [];

    public function add(\M2E\TikTokShop\Model\Promotion $promotion): self
    {
        $this->promotions[$promotion->getPromotionId()] = $promotion;

        return $this;
    }

    public function has(string $id): bool
    {
        return isset($this->promotions[$id]);
    }

    public function get(string $id): \M2E\TikTokShop\Model\Promotion
    {
        return $this->promotions[$id];
    }

    public function remove(string $id): self
    {
        unset($this->promotions[$id]);

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion[]
     */
    public function getAll(): array
    {
        return array_values($this->promotions);
    }
}
