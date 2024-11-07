<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class ProductType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        $typeNames = [
            \M2E\TikTokShop\Helper\Magento\Product::TYPE_SIMPLE => __('Simple Product'),
            \M2E\TikTokShop\Helper\Magento\Product::TYPE_CONFIGURABLE => __('Configurable Product'),
        ];

        foreach ($typeNames as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }
}
