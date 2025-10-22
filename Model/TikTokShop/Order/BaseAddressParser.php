<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order;

class BaseAddressParser
{
    private array $serverData;
    private \M2E\TikTokShop\Model\TikTokShop\Order\DistrictsCollection $districtsCollection;

    public function __construct(array $serverData)
    {
        $this->serverData = $serverData;
        $this->districtsCollection = \M2E\TikTokShop\Model\TikTokShop\Order\DistrictsCollection::createFromArray(
            $serverData['recipient_address']['district_info'] ?? []
        );
    }

    public function getCity(): string
    {
        return $this->getDistrictsCollection()->tryFindLevelName('county') ?? '';
    }

    public function getState(): string
    {
        return '';
    }

    public function getBuyerName(): string
    {
        return $this->serverData['recipient_address']['name'] ?? '';
    }

    public function getBuyerEmail(): string
    {
        return trim($this->serverData['buyer_email'] ?? '');
    }

    public function getRecipientName(): string
    {
        return $this->getBuyerName();
    }

    public function getStreetLines(): array
    {
        $streetLines = [];
        $streetLines[] = $this->serverData['recipient_address']['address_line1'];
        $streetLines[] = $this->serverData['recipient_address']['address_line2'];
        $streetLines[] = $this->serverData['recipient_address']['address_line3'];
        $streetLines[] = $this->serverData['recipient_address']['address_line4'];

        return $streetLines;
    }

    public function getCountryCode(): string
    {
        return $this->serverData['recipient_address']['region_code'] ?? '';
    }

    public function getDistrictsCollection(): \M2E\TikTokShop\Model\TikTokShop\Order\DistrictsCollection
    {
        return $this->districtsCollection;
    }

    public function getPostalCode(): string
    {
        return $this->serverData['recipient_address']['postal_code'] ?? '';
    }

    public function getPhone(): string
    {
        return $this->serverData['recipient_address']['phone_number'] ?? '';
    }
}
