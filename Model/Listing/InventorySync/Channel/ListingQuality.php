<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel;

class ListingQuality
{
    private ?string $tier;
    /** @var \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality\RecommendationCollection */
    private ListingQuality\RecommendationCollection $recommendationCollection;

    public function __construct(?string $tier)
    {
        $this->tier = $tier;
        $this->recommendationCollection = new ListingQuality\RecommendationCollection();
    }

    public function addRecommendation(ListingQuality\Recommendation $recommendation)
    {
        $this->recommendationCollection->add($recommendation);
    }

    public function getRecommendationCollection(): ListingQuality\RecommendationCollection
    {
        return $this->recommendationCollection;
    }

    public function hasTier(): bool
    {
        return $this->tier !== null;
    }

    public function getTier(): ?string
    {
        return $this->tier;
    }
}
