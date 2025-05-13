<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\ResponsiblePerson;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;

    public function __construct(string $accountHash)
    {
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['responsiblePerson', 'get', 'entities'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection {
        $collection = new \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection();

        foreach ($response->getResponseData()['responsible_persons'] ?? [] as $responsiblePersonData) {
            $collection->add(
                new \M2E\TikTokShop\Model\Channel\ResponsiblePerson(
                    $responsiblePersonData['id'],
                    $responsiblePersonData['name'],
                    $responsiblePersonData['email'],
                    $responsiblePersonData['phone_number']['country_code'],
                    $responsiblePersonData['phone_number']['local_number'],
                    $responsiblePersonData['address']['street_address_line1'],
                    $responsiblePersonData['address']['postal_code'],
                    $responsiblePersonData['address']['country'],
                )
            );
        }

        return $collection;
    }
}
