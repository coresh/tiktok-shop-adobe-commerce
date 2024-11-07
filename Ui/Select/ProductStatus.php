<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

use M2E\TikTokShop\Model\Product;

class ProductStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];

        $statuses = [
            Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
            Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
            Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
            Product::STATUS_BLOCKED => Product::getStatusTitle(Product::STATUS_BLOCKED),
        ];

        foreach ($statuses as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }
}
