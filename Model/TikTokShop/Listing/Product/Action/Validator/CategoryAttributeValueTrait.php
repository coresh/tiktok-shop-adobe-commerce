<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

trait CategoryAttributeValueTrait
{
    private function getAttributeValue(
        \M2E\TikTokShop\Model\Category\CategoryAttribute $attribute,
        \M2E\TikTokShop\Model\Magento\Product\Cache $product
    ): ?string {
        switch ($attribute->getValueMode()) {
            case \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_VALUE:
                $value = $attribute->getCustomValue();
                break;
            case \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE:
                $attributeCode = $attribute->getCustomAttributeValue();
                $value = $product->getAttributeValue($attributeCode);
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
