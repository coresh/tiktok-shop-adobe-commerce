<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\Manufacturer;

class UpdateCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;
    private \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer;

    public function __construct(
        string $accountHash,
        \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
    ) {
        $this->accountHash = $accountHash;
        $this->manufacturer = $manufacturer;
    }

    public function getCommand(): array
    {
        return ['manufacturer', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'manufacturer' => [
                'id' => $this->manufacturer->id,
                'name' => $this->manufacturer->name,
                'registered_trade_name' => $this->manufacturer->registeredTradeName,
                'email' => $this->manufacturer->email,
                'phone_number' => [
                    'country_code' => $this->manufacturer->phoneCountryCode,
                    'local_number' => $this->manufacturer->phoneLocalNumber,
                ],
                'address' => $this->manufacturer->address,
            ],
        ];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\Connector\Response {
        if ($response->getMessageCollection()->hasErrors()) {
            throw new \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData($response->getMessageCollection());
        }

        return $response;
    }
}
