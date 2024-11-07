<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop;

class OnlineSku extends \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'tts_online_sku';

    public function getLabel(): string
    {
        return (string)__('SKU');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('online_sku');
    }
}
