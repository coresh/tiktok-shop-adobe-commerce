<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Search;

class ResultCollection
{
    /** @var ResultItem[] */
    private array $items = [];
    private int $collectionSizeLimit;

    public function __construct(int $collectionSizeLimit)
    {
        $this->collectionSizeLimit = $collectionSizeLimit;
    }

    public function add(ResultItem $item): void
    {
        if ($this->getCount() >= $this->collectionSizeLimit) {
            return;
        }

        if (isset($this->items[$item->categoryId])) {
            return;
        }

        $this->items[$item->categoryId] = $item;
    }

    /**
     * @return ResultItem[]
     */
    public function getAll(): array
    {
        return array_values($this->items);
    }

    public function getCount(): int
    {
        return count($this->items);
    }
}
