<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Promotion\Get;

class Response
{
    /** @var \M2E\TikTokShop\Model\Promotion\Channel\Promotion[] */
    private array $promotions = [];

    public function __construct(array $promotions)
    {
        $this->promotions = $promotions;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion\Channel\Promotion[]
     */
    public function getPromotions(): array
    {
        return $this->promotions;
    }
}
