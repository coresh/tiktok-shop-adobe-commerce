<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Add;

class Response
{
    private string $hash;
    private string $accountOpenId;
    private string $accountName;
    private string $accountRegion;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop[] */
    private array $shops;

    public function __construct(
        string $hash,
        string $accountOpenId,
        string $accountName,
        string $accountRegion,
        array $shops
    ) {
        $this->hash = $hash;
        $this->accountOpenId = $accountOpenId;
        $this->accountName = $accountName;
        $this->accountRegion = $accountRegion;
        $this->shops = $shops;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getAccountOpenId(): string
    {
        return $this->accountOpenId;
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function getAccountRegion(): string
    {
        return $this->accountRegion;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop[]
     */
    public function getShops(): array
    {
        return $this->shops;
    }
}
