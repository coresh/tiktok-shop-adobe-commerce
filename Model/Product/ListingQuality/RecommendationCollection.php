<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\ListingQuality;

class RecommendationCollection
{
    /** @var Recommendation[] */
    private array $recommendations = [];

    public function add(Recommendation $recommendation)
    {
        $this->recommendations[] = $recommendation;
    }

    public function empty(): bool
    {
        return count($this->recommendations) === 0;
    }

    /**
     * @return Recommendation[]
     */
    public function getAll(): array
    {
        return $this->recommendations;
    }

    /**
     * @return Recommendation[][]
     */
    public function getGroupedBySection(): array
    {
        $result = [];
        foreach ($this->recommendations as $recommendation) {
            $result[$recommendation->section][] = $recommendation;
        }

        return $result;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->recommendations as $recommendation) {
            $result[] = [
                'code' => $recommendation->code,
                'field' => $recommendation->field,
                'section' => $recommendation->section,
                'how_to_solve' => $recommendation->howToSolve,
                'quality_tier' => $recommendation->qualityTier,
            ];
        }

        return $result;
    }
}
