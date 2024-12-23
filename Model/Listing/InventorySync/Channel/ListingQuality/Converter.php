<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality;

class Converter
{
    public function toProductListingQuality(
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality $channelListingQuality
    ): \M2E\TikTokShop\Model\Product\ListingQuality {
        $productListingQuality = new \M2E\TikTokShop\Model\Product\ListingQuality($channelListingQuality->getTier());

        foreach ($channelListingQuality->getRecommendationCollection()->getAll() as $channelRecommendation) {
            $productRecommendation = new \M2E\TikTokShop\Model\Product\ListingQuality\Recommendation(
                $channelRecommendation->code,
                $channelRecommendation->field,
                $channelRecommendation->section,
                $channelRecommendation->howToSolve,
                $channelRecommendation->qualityTier,
            );

            $productListingQuality->addRecommendation($productRecommendation);
        }

        return $productListingQuality;
    }
}
