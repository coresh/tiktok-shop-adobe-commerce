<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class ProductPromotionService
{
    /** @var \M2E\TikTokShop\Model\Promotion\Product\Repository */
    private Promotion\Product\Repository $promotionProductRepository;

    public function __construct(\M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository)
    {
        $this->promotionProductRepository = $promotionProductRepository;
    }

    public function isProductOnPromotion(Product $product): bool
    {
        return $this->promotionProductRepository->isExistActiveOrNotStartPromotionForProduct(
            $product->getTTSProductId(),
            $product->getAccount()->getId(),
            $product->getShop()->getId(),
        );
    }

    public function isProductVariantOnPromotion(
        Product\VariantSku $variant,
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): bool {
        return $this->promotionProductRepository->isExistActiveOrNotStartPromotionForSku(
            $variant->getSkuId(),
            $account->getId(),
            $shop->getId(),
        );
    }
}
