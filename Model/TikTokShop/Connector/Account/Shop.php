<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account;

class Shop
{
    public const CROSS_BORDER = 'CROSS_BORDER';
    public const LOCAL_TO_LOCAL = 'LOCAL';

    private string $shopId;
    private string $shopName;
    private string $region;
    private string $type;

    public function __construct(
        string $shopId,
        string $shopName,
        string $region,
        string $type
    ) {
        $this->shopId = $shopId;
        $this->shopName = $shopName;
        $this->region = $region;

        if (!in_array($type, [self::LOCAL_TO_LOCAL, self::CROSS_BORDER])) {
            throw new \LogicException("Shop type '$type' is not valid.");
        }

        $this->type = $type;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getShopName(): string
    {
        return $this->shopName;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
