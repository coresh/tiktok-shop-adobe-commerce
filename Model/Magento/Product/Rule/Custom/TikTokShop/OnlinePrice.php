<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop;

use M2E\TikTokShop\Model\Magento\Product\Rule\Condition\AbstractModel;

class OnlinePrice extends \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'tts_online_price';

    public function getLabel(): string
    {
        return (string)__('Price');
    }

    public function getInputType(): string
    {
        return AbstractModel::INPUT_TYPE_PRICE;
    }

    public function getValueElementType(): string
    {
        return AbstractModel::VALUE_ELEMENT_TYPE_TEXT;
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product): array
    {
        return [
            $product->getData('online_min_price'),
            $product->getData('online_max_price'),
        ];
    }
}
