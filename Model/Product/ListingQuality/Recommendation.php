<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\ListingQuality;

class Recommendation
{
    private const TIRE_GOOD = 'GOOD';
    private const TIRE_POOR = 'POOR';
    private const TIRE_FAIR = 'FAIR';

    public string $code;
    public string $field;
    public string $section;
    public string $howToSolve;
    public string $qualityTier;

    public function __construct(
        string $code,
        string $field,
        string $section,
        string $howToSolve,
        string $qualityTier
    ) {
        $this->code = $code;
        $this->field = $field;
        $this->section = $section;
        $this->howToSolve = $howToSolve;
        $this->qualityTier = $qualityTier;
    }

    public function getTireLabel(): string
    {
        $labels = [
            self::TIRE_GOOD => (string)__('Good'),
            self::TIRE_FAIR => (string)__('Fair'),
            self::TIRE_POOR => (string)__('Poor'),
        ];

        return $labels[$this->qualityTier] ?? (string)__('N/A');
    }
}
