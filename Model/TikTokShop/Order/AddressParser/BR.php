<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\AddressParser;

class BR extends \M2E\TikTokShop\Model\TikTokShop\Order\BaseAddressParser
{
    public function getState(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('state') ?? '';
    }

    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('municipality') ?? '';
    }
}
