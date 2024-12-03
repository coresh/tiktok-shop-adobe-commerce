<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\Manufacturer;

class GetCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;

    public function __construct(string $accountHash)
    {
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['manufacturer', 'get', 'entities'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
        ];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\Channel\Manufacturer\Collection {
        $collection = new \M2E\TikTokShop\Model\Channel\Manufacturer\Collection();

        foreach ($response->getResponseData()['manufacturers'] ?? [] as $manufacturerData) {
            $collection->add(
                new \M2E\TikTokShop\Model\Channel\Manufacturer(
                    $manufacturerData['id'],
                    $manufacturerData['name'],
                    $manufacturerData['registered_trade_name'],
                    $manufacturerData['email'],
                    $manufacturerData['phone_number']['country_code'],
                    $manufacturerData['phone_number']['local_number'],
                    $manufacturerData['address']
                )
            );
        }

        return $collection;
    }
}
