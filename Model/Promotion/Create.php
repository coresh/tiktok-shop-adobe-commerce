<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

class Create
{
    private Repository $repository;
    private \M2E\TikTokShop\Model\PromotionFactory $factory;

    public function __construct(
        Repository $repository,
        \M2E\TikTokShop\Model\PromotionFactory $factory
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channelPromotion
    ): \M2E\TikTokShop\Model\Promotion {
        $promotion = $this->factory->create();
        $promotion->init(
            $account->getId(),
            $shop->getId(),
            $channelPromotion->getPromotionId(),
            $channelPromotion->getTitle(),
            $channelPromotion->getType(),
            $channelPromotion->getStatus(),
            $channelPromotion->getProductLevel(),
            $channelPromotion->getStartDate(),
            $channelPromotion->getEndDate(),
        );

        $this->repository->create($promotion);

        return $promotion;
    }
}
