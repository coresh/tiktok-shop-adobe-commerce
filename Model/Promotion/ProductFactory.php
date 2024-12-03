<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

class ProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Product
    {
        return $this->objectManager->create(Product::class);
    }

    public function createAsProduct(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Product $channel
    ): Product {
        $model = $this->createEmpty();
        $model->createAsProduct(
            $promotion,
            $channel->getProductId(),
            $channel->getFixedPrice(),
            $channel->getDiscount(),
            $channel->getQuantityLimit(),
            $channel->getPerUser()
        );

        return $model;
    }

    public function createAsSku(
        \M2E\TikTokShop\Model\Promotion $promotion,
        \M2E\TikTokShop\Model\Promotion\Channel\Product $channel,
        \M2E\TikTokShop\Model\Promotion\Channel\Sku $channelSku
    ): Product {
        $model = $this->createEmpty();
        $model->createAsSku(
            $promotion,
            $channel->getProductId(),
            $channelSku->getSkuId(),
            $channelSku->getPrice(),
            $channelSku->getDiscount(),
            $channelSku->getQuantityLimit(),
            $channelSku->getPerUser()
        );

        return $model;
    }
}
