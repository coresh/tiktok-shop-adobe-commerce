<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality;

class RecommendationCollection
{
    /** @var Recommendation[] */
    private array $recommendations = [];

    public function add(Recommendation $recommendation)
    {
        $this->recommendations[] = $recommendation;
    }

    /**
     * @return Recommendation[]
     */
    public function getAll(): array
    {
        return $this->recommendations;
    }
}
