<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Product;

class Synchronization
{
    private \M2E\TikTokShop\Model\Promotion\Product\Create $promotionProductCreateService;
    private \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository;
    private \M2E\TikTokShop\Model\Promotion\ProductLogService $logService;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\Product\Create $promotionProductCreateService,
        \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository,
        \M2E\TikTokShop\Model\Promotion\ProductLogService $logService
    ) {
        $this->promotionProductCreateService = $promotionProductCreateService;
        $this->promotionProductRepository = $promotionProductRepository;
        $this->logService = $logService;
    }

    public function removeOldProducts(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel,
        bool $isNeedAddProductLog
    ): void {

        if ($promotion->isProductLevelByProduct()) {
            $this->removeOldProductsByProduct($promotion, $channel, $isNeedAddProductLog);
        } elseif ($promotion->isProductLevelByVariation()) {
            $this->removeOldProductsBySku($promotion, $channel, $isNeedAddProductLog);
        }
    }

    private function removeOldProductsByProduct(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel,
        bool $isNeedAddProductLog
    ): void {
        $existProductIds = [];
        foreach ($channel->getPromotionProducts() as $promotionProduct) {
            $existProductIds[] = $promotionProduct->getProductId();
        }

        $removedProducts = $this->promotionProductRepository->findOldProducts($promotion, $existProductIds);
        if (empty($removedProducts)) {
            return;
        }

        if ($isNeedAddProductLog) {
            $this->logService->addLogForRemovedProductsAboutRemoveFromPromotion($promotion, $removedProducts);
        }

        foreach ($removedProducts as $removedProduct) {
            $this->promotionProductRepository->remove($removedProduct);
        }
    }

    private function removeOldProductsBySku(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel,
        bool $isNeedAddProductLog
    ): void {
        $existProductSkuIds = [];
        foreach ($channel->getPromotionProducts() as $promotionProduct) {
            $promotionProductSkus = $promotionProduct->getPromotionProductSkus();

            foreach ($promotionProductSkus as $promotionProductSku) {
                $existProductSkuIds[] = $promotionProductSku->getSkuId();
            }

            $removedProducts = $this->promotionProductRepository->findOldSkus($promotion, $existProductSkuIds);
            if (empty($removedProducts)) {
                continue;
            }

            if ($isNeedAddProductLog) {
                $this->logService->addLogForRemovedProductsAboutRemoveFromPromotion($promotion, $removedProducts);
            }

            foreach ($removedProducts as $removedProduct) {
                $this->promotionProductRepository->remove($removedProduct);
            }
        }
    }

    public function addNewProducts(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel,
        bool $isNeedAddProductLog
    ): void {
        if ($promotion->isProductLevelByProduct()) {
            $this->addNewProductsForPromotionProduct($promotion, $channel, $isNeedAddProductLog);
        } elseif ($promotion->isProductLevelByVariation()) {
            $this->addNewProductForPromotionSku($promotion, $channel, $isNeedAddProductLog);
        }
    }

    private function addNewProductsForPromotionProduct(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel,
        bool $isNeedAddProductLog
    ): void {
        $existProductsById = [];
        foreach ($promotion->getProducts() as $product) {
            $existProductsById[$product->getProductId()] = $product;
        }

        $createdProducts = [];
        foreach ($channel->getPromotionProducts() as $channelProduct) {
            if (isset($existProductsById[$channelProduct->getProductId()])) {
                continue;
            }

            $createdProducts[] = $this->promotionProductCreateService->createProduct(
                $promotion,
                $channelProduct
            );
        }

        if ($isNeedAddProductLog && !empty($createdProducts)) {
            $this->logService->addLogForAddedProductsAboutAddToPromotion($promotion, $createdProducts);
        }
    }

    private function addNewProductForPromotionSku(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel,
        bool $isNeedAddProductLog
    ): void {
        $existSkusById = [];
        foreach ($promotion->getSkus() as $sku) {
            $existSkusById[$sku->getSkuId()] = $sku;
        }

        $createdSkus = [];
        foreach ($channel->getPromotionProducts() as $channelProduct) {
            foreach ($channelProduct->getPromotionProductSkus() as $channelSku) {
                if (isset($existSkusById[$channelSku->getSkuId()])) {
                    continue;
                }

                $createdSkus[] = $this->promotionProductCreateService->createSku(
                    $promotion,
                    $channelProduct,
                    $channelSku
                );
            }
        }

        if ($isNeedAddProductLog && !empty($createdSkus)) {
            $this->logService->addLogForAddedProductsAboutAddToPromotion($promotion, $createdSkus);
        }
    }
}
