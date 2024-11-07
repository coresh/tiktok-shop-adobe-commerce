<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop;

class ProductId extends \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'tts_product_id';

    public function getLabel(): string
    {
        return (string)__('Product Id');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('product_id');
    }
}
