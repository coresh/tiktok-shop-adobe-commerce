<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\Ui;

class RuntimeStorage
{
    private array $products;
    private \M2E\TikTokShop\Model\Product\Repository $repository;

    public function __construct(\M2E\TikTokShop\Model\Product\Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int[] $ids
     *
     * @return void
     */
    public function loadByIds(array $ids): void
    {
        $products = [];
        foreach ($this->repository->findByIds($ids) as $product) {
            $products[$product->getId()] = $product;
        }

        $this->products = $products;
    }

    public function findProduct(int $id): ?\M2E\TikTokShop\Model\Product
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->products)) {
            return null;
        }

        /** @psalm-suppress RedundantCondition */
        return $this->products[$id] ?? null;
    }

    public function getAll(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->products)) {
            throw new \LogicException('Products was not initialized.');
        }

        return $this->products;
    }
}
