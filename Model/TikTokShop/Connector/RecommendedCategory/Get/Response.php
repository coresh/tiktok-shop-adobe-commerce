<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\Get;

class Response
{
    private ?string $geCategoryId;

    public function __construct(
        ?string $geCategoryId
    ) {
        $this->geCategoryId = $geCategoryId;
    }

    public function geCategoryId(): ?string
    {
        return $this->geCategoryId;
    }
}
