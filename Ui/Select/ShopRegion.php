<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class ShopRegion implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];

        $regionsNames = [
            \M2E\TikTokShop\Model\Shop::REGION_GB => __('United Kingdom'),
            \M2E\TikTokShop\Model\Shop::REGION_US => __('United States'),
            \M2E\TikTokShop\Model\Shop::REGION_ES => __('Spain'),
        ];

        foreach (array_keys($regionsNames) as $region) {
            $options[] = [
                'label' => $regionsNames[$region],
                'value' => $region,
            ];
        }

        return $options;
    }
}
