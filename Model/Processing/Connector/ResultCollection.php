<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Connector;

class ResultCollection
{
    /** @var Result[] */
    private array $results = [];

    public function add(Result $result): void
    {
        $this->results[$result->getHash()] = $result;
    }

    public function has(string $hash)
    {
        return isset($this->results[$hash]);
    }

    public function get(string $hash): Result
    {
        return $this->results[$hash];
    }

    public function getAll(): array
    {
        return $this->results;
    }
}
