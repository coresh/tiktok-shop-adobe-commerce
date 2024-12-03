<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Channel;

class PromotionCollection
{
    /** @var Promotion[] */
    private array $promotions = [];

    public function add(Promotion $promotion): self
    {
        $this->promotions[$promotion->getPromotionId()] = $promotion;

        return $this;
    }

    public function has(string $id): bool
    {
        return isset($this->promotions[$id]);
    }

    public function get(string $id): Promotion
    {
        return $this->promotions[$id];
    }

    public function remove(string $id): self
    {
        unset($this->promotions[$id]);

        return $this;
    }

    /**
     * @return Promotion[]
     */
    public function getAll(): array
    {
        return array_values($this->promotions);
    }
}
