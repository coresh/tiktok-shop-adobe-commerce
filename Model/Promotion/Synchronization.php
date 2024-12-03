<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

use M2E\TikTokShop\Model\Promotion\PromotionCollection as ExistCollection;
use M2E\TikTokShop\Model\Promotion\Channel\PromotionCollection as ChannelCollection;

class Synchronization
{
    private \M2E\TikTokShop\Model\Promotion\Repository $repository;
    private \M2E\TikTokShop\Model\Promotion\Delete $deleteService;
    private \M2E\TikTokShop\Model\Promotion\UpdateFromChannel $updateFromChannelService;
    private \M2E\TikTokShop\Model\Promotion\Create $promotionCreateService;
    private Channel\GetPromotion $getChannelPromotion;
    /** @var \M2E\TikTokShop\Model\Promotion\Product\Create */
    private Product\Create $productCreateService;
    /** @var \M2E\TikTokShop\Model\Promotion\ProductLogService */
    private ProductLogService $productLogService;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\Repository $repository,
        \M2E\TikTokShop\Model\Promotion\Channel\GetPromotion $getChannelPromotion,
        \M2E\TikTokShop\Model\Promotion\Delete $deleteService,
        \M2E\TikTokShop\Model\Promotion\UpdateFromChannel $updateFromChannelService,
        \M2E\TikTokShop\Model\Promotion\Create $promotionCreateService,
        \M2E\TikTokShop\Model\Promotion\Product\Create $productCreateService,
        \M2E\TikTokShop\Model\Promotion\ProductLogService $productLogService
    ) {
        $this->repository = $repository;
        $this->deleteService = $deleteService;
        $this->updateFromChannelService = $updateFromChannelService;
        $this->promotionCreateService = $promotionCreateService;
        $this->getChannelPromotion = $getChannelPromotion;
        $this->productCreateService = $productCreateService;
        $this->productLogService = $productLogService;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): void {
        $channelCollection = $this->getChannelPromotion->getPromotions($account, $shop);
        $existCollection = $this->repository->findByAccountAndShop(
            $account->getId(),
            $shop->getId()
        );

        $this->removePromotions($channelCollection, $existCollection);
        $this->updatePromotions($channelCollection, $existCollection);
        $this->createPromotions($channelCollection, $account, $shop, $existCollection);
    }

    private function removePromotions(
        ChannelCollection $channelPromotions,
        ExistCollection $existPromotions
    ): void {
        foreach ($existPromotions->getAll() as $existPromotion) {
            if ($channelPromotions->has($existPromotion->getPromotionId())) {
                continue;
            }

            $this->deleteService->process($existPromotion);
            $existPromotions->remove($existPromotion->getPromotionId());
        }
    }

    private function updatePromotions(
        ChannelCollection $channelPromotions,
        ExistCollection $existPromotions
    ): void {
        foreach ($existPromotions->getAll() as $existPromotion) {
            $promotionId = $existPromotion->getPromotionId();

            if (!$channelPromotions->has($promotionId)) {
                continue;
            }

            $this->updateFromChannelService->process(
                $existPromotion,
                $channelPromotions->get($promotionId),
            );

            $channelPromotions->remove($promotionId);
        }
    }

    private function createPromotions(
        ChannelCollection $channelPromotions,
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        ExistCollection $existPromotions
    ): void {
        foreach ($channelPromotions->getAll() as $channelPromotion) {
            $promotion = $this->promotionCreateService->process($account, $shop, $channelPromotion);
            if ($promotion->isProductLevelByProduct()) {
                foreach ($channelPromotion->getPromotionProducts() as $channelProduct) {
                    $this->productCreateService->createProduct(
                        $promotion,
                        $channelProduct
                    );
                }
            } elseif ($promotion->isProductLevelByVariation()) {
                foreach ($channelPromotion->getPromotionProducts() as $channelProduct) {
                    foreach ($channelProduct->getPromotionProductSkus() as $channelSku) {
                        $this->productCreateService->createSku($promotion, $channelProduct, $channelSku);
                    }
                }
            }

            if ($promotion->isStatusActive()) {
                $this->productLogService->addLogForAllProductsAboutAddToPromotion($promotion);
            }

            $existPromotions->add($promotion);
        }
    }
}
