<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality;

class Recommendation
{
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
}
