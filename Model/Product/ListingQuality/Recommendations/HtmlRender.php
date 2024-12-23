<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\ListingQuality\Recommendations;

use M2E\TikTokShop\Model\Product\ListingQuality\RecommendationCollection;

class HtmlRender
{
    public function render(RecommendationCollection $recommendationCollection): string
    {
        $html = '<div class="tts-listing-quality-recomendations">';
        foreach ($recommendationCollection->getGroupedBySection() as $section => $recommendations) {
            $html .= '<div>';
            $html .= "<h2>$section</h2>";
            $html .= '<ul class="tts-listing-quality-recomendation-list">';
            foreach ($recommendations as $recommendation) {
                $html .= "<li>$recommendation->howToSolve ({$recommendation->getTireLabel()})</li>";
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
