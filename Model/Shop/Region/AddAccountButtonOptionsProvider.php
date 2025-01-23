<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop\Region;

class AddAccountButtonOptionsProvider
{
    /**
     * @return array{ array{id: string, label: string, region_code: string} }
     */
    public function retrieve(): array
    {
        return [
            [
                'id' => 'europe_uk',
                'label' => (string)__('Europe/UK'),
                'region_code' => \M2E\TikTokShop\Model\Shop\Region::REGION_GB
            ],
            [
                'id' => 'united_states',
                'label' => (string)__('United States'),
                'region_code' => \M2E\TikTokShop\Model\Shop\Region::REGION_US
            ],
            [
                'id' => 'mexico',
                'label' => (string)__('Mexico'),
                'region_code' => \M2E\TikTokShop\Model\Shop\Region::REGION_MX
            ]
        ];
    }
}
