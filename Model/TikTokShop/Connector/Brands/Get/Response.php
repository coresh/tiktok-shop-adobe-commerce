<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get;

class Response
{
    private array $brands;
    private int $totalCount;
    private ?string $nextPageToken;

    public function __construct(
        array $brands,
        int $totalCount,
        ?string $nextPageToken
    ) {
        $this->brands = $brands;
        $this->totalCount = $totalCount;
        $this->nextPageToken = $nextPageToken;
    }

    public function getBrands(): array
    {
        return $this->brands;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getNextPageToken(): ?string
    {
        return $this->nextPageToken;
    }
}
