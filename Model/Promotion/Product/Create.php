<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Product;

class Create
{
    private \M2E\TikTokShop\Model\Promotion\ProductFactory $promotionProductFactory;
    private \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\ProductFactory $promotionProductFactory,
        \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository
    ) {
        $this->promotionProductFactory = $promotionProductFactory;
        $this->promotionProductRepository = $promotionProductRepository;
    }

    public function createProduct(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Product $channelProduct
    ): \M2E\TikTokShop\Model\Promotion\Product {
        $product = $this->promotionProductFactory->createAsProduct($promotion, $channelProduct);
        $this->promotionProductRepository->create($product);

        return $product;
    }

    public function createSku(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Product $channelProduct,
        \M2E\TikTokShop\Model\Promotion\Channel\Sku $channelSku
    ): \M2E\TikTokShop\Model\Promotion\Product {
        $sku = $this->promotionProductFactory->createAsSku($promotion, $channelProduct, $channelSku);
        $this->promotionProductRepository->create($sku);

        return $sku;
    }
}
