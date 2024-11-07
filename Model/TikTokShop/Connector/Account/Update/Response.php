<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Update;

class Response
{
    /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop[] */
    private array $shops;
    private string $sellerName;

    public function __construct(array $shops, string $sellerName)
    {
        $this->sellerName = $sellerName;
        $this->shops = $shops;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop[]
     */
    public function getShops(): array
    {
        return $this->shops;
    }

    public function getSellerName(): string
    {
        return $this->sellerName;
    }
}
