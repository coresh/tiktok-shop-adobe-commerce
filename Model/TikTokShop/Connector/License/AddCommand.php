<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\License;

class AddCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private Add\Request $request;

    public function __construct(Add\Request $request)
    {
        $this->request = $request;
    }

    public function getCommand(): array
    {
        return ['license', 'add', 'record'];
    }

    public function getRequestData(): array
    {
        return [
            'domain' => $this->request->getDomain(),
            'directory' => $this->request->getdirectory(),
            'email' => $this->request->getEmail(),
            'first_name' => $this->request->getFirstName(),
            'last_name' => $this->request->getLastName(),
            'phone' => $this->request->getPhone(),
            'country' => $this->request->getCountry(),
            'city' => $this->request->getCity(),
            'postal_code' => $this->request->getPostalCode(),
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return new Add\Response($response->getResponseData()['key']);
    }
}
