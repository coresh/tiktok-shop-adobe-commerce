<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\ResponsiblePerson;

class AddCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;
    private \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson;

    public function __construct(
        string $accountHash,
        \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
    ) {
        $this->accountHash = $accountHash;
        $this->responsiblePerson = $responsiblePerson;
    }

    public function getCommand(): array
    {
        return ['responsiblePerson', 'create', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'responsible_person' => [
                'name' => $this->responsiblePerson->name,
                'email' => $this->responsiblePerson->email,
                'phone_number' => [
                    'country_code' => $this->responsiblePerson->phoneCountryCode,
                    'local_number' => $this->responsiblePerson->phoneLocalNumber,
                ],
                'address' => [
                    'street_address_line1' => $this->responsiblePerson->streetAddressLine1,
                    'street_address_line2' => $this->responsiblePerson->streetAddressLine2,
                    'district' => $this->responsiblePerson->district,
                    'city' => $this->responsiblePerson->city,
                    'postal_code' => $this->responsiblePerson->postalCode,
                    'province' => $this->responsiblePerson->province,
                    'country' => $this->responsiblePerson->country,
                ],
            ],
        ];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\Channel\ResponsiblePerson {
        if ($response->getMessageCollection()->hasErrors()) {
            throw new \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData($response->getMessageCollection());
        }

        $responseData = $response->getResponseData();

        $this->responsiblePerson->id = $responseData['id'];

        return $this->responsiblePerson;
    }
}
