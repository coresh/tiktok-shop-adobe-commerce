<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

class UpdateFromChannel
{
    private \M2E\TikTokShop\Model\Promotion\Repository $repository;
    private \M2E\TikTokShop\Model\Promotion\ProductLogService $logService;
    /** @var \M2E\TikTokShop\Model\Promotion\Product\Synchronization */
    private Product\Synchronization $productSynchronization;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\Repository $repository,
        \M2E\TikTokShop\Model\Promotion\ProductLogService $logService,
        \M2E\TikTokShop\Model\Promotion\Product\Synchronization $productSynchronization
    ) {
        $this->logService = $logService;
        $this->repository = $repository;
        $this->productSynchronization = $productSynchronization;
    }

    public function process(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channelPromotion
    ): void {
        $isOldStatusActive = $promotion->isStatusActive();

        $isUpdated = $promotion->updateFromChannel($channelPromotion);
        if ($isUpdated) {
            $this->repository->save($promotion);
        }

        $this->removeOldProducts($promotion, $channelPromotion, $isOldStatusActive);
        $this->addNewProducts($promotion, $channelPromotion, $isOldStatusActive);

        if (
            !$isOldStatusActive
            && $promotion->isStatusActive()
        ) {
            $this->addLogForAllProducts($promotion);
        }
    }

    private function removeOldProducts(
        \M2E\TikTokShop\Model\Promotion $promotion,
        Channel\Promotion $channelPromotion,
        bool $isOldStatusActive
    ): void {
        $this->productSynchronization->removeOldProducts($promotion, $channelPromotion, $isOldStatusActive);
    }

    private function addNewProducts(
        \M2E\TikTokShop\Model\Promotion $promotion,
        Channel\Promotion $channelPromotion,
        bool $isOldStatusActive
    ) {
        $this->productSynchronization->addNewProducts($promotion, $channelPromotion, $isOldStatusActive);
    }

    private function addLogForAllProducts(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $this->logService->addLogForAllProductsAboutAddToPromotion($promotion);
    }
}
