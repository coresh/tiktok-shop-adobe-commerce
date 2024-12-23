<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class ListingQuality
{
    public const TIER_POOR = 'POOR';
    public const TIER_FAIR = 'FAIR';
    public const TIER_GOOD = 'GOOD';

    private ?string $tier;
    /** @var \M2E\TikTokShop\Model\Product\ListingQuality\RecommendationCollection */
    private ListingQuality\RecommendationCollection $recommendationCollection;

    public function __construct(?string $tier)
    {
        $this->tier = $tier;
        $this->recommendationCollection = new ListingQuality\RecommendationCollection();
    }

    public function hasRecommendations(): bool
    {
        return !$this->recommendationCollection->empty();
    }

    public function addRecommendation(ListingQuality\Recommendation $recommendation)
    {
        $this->recommendationCollection->add($recommendation);
    }

    public function getRecommendationCollection(): ListingQuality\RecommendationCollection
    {
        return $this->recommendationCollection;
    }

    public function isTierPoor(): bool
    {
        return $this->tier === self::TIER_POOR;
    }

    public function isTierFair(): bool
    {
        return $this->tier === self::TIER_FAIR;
    }

    public function isTierGood(): bool
    {
        return $this->tier === self::TIER_GOOD;
    }

    public function hasTier(): bool
    {
        return $this->tier !== null;
    }

    public function getTier(): ?string
    {
        return $this->tier;
    }

    // ----------------------------------------

    public static function getTierLabel(?string $tier): string
    {
        $labels = [
            self::TIER_GOOD => (string)__('Good'),
            self::TIER_POOR => (string)__('Poor'),
            self::TIER_FAIR => (string)__('Fair'),
        ];

        return $labels[$tier] ?? (string)__('N/A');
    }
}
