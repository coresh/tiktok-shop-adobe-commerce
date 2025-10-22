<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class GB extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('county') ?? '';
    }

    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('country') ?? '';
    }
}
