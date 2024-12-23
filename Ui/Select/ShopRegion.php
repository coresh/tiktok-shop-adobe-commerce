<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class ShopRegion implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];
        $regionsNames = \M2E\TikTokShop\Model\Shop::getAvailableRegions();

        foreach ($regionsNames as $code => $name) {
            $options[] = [
                'label' => $name,
                'value' => $code,
            ];
        }

        return $options;
    }
}
