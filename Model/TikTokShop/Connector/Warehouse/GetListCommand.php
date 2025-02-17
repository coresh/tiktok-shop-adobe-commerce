<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse;

class GetListCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;

    public function __construct(string $accountHash, string $shopId)
    {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
    }

    public function getCommand(): array
    {
        return ['warehouse', 'get', 'list'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): GetList\Response
    {
        $result = new GetList\Response();
        foreach ($response->getResponseData() as $warehouseData) {
            $address = $warehouseData['address'];
            $warehouseAddress = [
                'region' => $address['region'] ?? null,
                'state' => $address['state'] ?? null,
                'city' => $address['city'] ?? null,
                'district' => $address['district'] ?? null,
                'town' => $address['town'] ?? null,
                'phone' => $address['phone'] ?? null,
                'contactPerson' => $address['contact_person'] ?? null,
                'zipCode' => $address['zip_code'] ?? null,
                'fullAddress' => $address['full_address'] ?? null,
                'regionCode' => $address['region_code'] ?? null,
            ];

            $result->addWarehouse(
                new \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\Warehouse(
                    $warehouseData['id'],
                    $warehouseData['name'],
                    $warehouseData['effect_status'],
                    $warehouseData['type'],
                    $warehouseData['sub_type'],
                    $warehouseData['is_default'],
                    $warehouseAddress
                )
            );
        }

        return $result;
    }
}
