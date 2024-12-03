<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Channel;

class GetPromotion
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Promotion\Get\Processor $promotionProcessor;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Promotion\Get\Processor $promotionProcessor
    ) {
        $this->promotionProcessor = $promotionProcessor;
    }

    public function getPromotions(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): PromotionCollection {
        $serverResponse = $this->promotionProcessor->process($account, $shop);

        $collection = new PromotionCollection();
        foreach ($serverResponse->getPromotions() as $promotion) {
            $collection->add($promotion);
        }

        return $collection;
    }
}
