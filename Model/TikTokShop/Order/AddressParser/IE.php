<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class IE extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('municipal district') ?? '';
    }

    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('county') ?? '';
    }
}
