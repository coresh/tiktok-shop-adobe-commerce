<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

class Delete
{
    private \M2E\TikTokShop\Model\Promotion\Repository $repository;
    private \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository;
    /** @var \M2E\TikTokShop\Model\Promotion\ProductLogService */
    private ProductLogService $logService;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\Repository $repository,
        \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository,
        ProductLogService $logService
    ) {
        $this->repository = $repository;
        $this->promotionProductRepository = $promotionProductRepository;
        $this->logService = $logService;
    }

    public function process(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        if (
            $promotion->isStatusActive()
            || $promotion->isActiveNow()
        ) {
            $this->logService->addLogForAllProductsAboutRemoveFromPromotion($promotion);
        }

        $this->promotionProductRepository->removeByPromotion($promotion);
        $this->repository->remove($promotion);
    }
}
