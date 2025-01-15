<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class ShopRegion implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection;

    public function __construct(\M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection)
    {
        $this->regionCollection = $regionCollection;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->regionCollection->getAll() as $region) {
            $options[] = [
                'label' => $region->getLabel(),
                'value' => $region->getRegionCode(),
            ];
        }

        return $options;
    }
}
