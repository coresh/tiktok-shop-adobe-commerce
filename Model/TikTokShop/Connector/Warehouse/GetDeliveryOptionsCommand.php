<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse;

use M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DeliveryOption;
use M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DimensionLimit;
use M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\WeightLimit;

class GetDeliveryOptionsCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;
    private string $warehouseId;

    public function __construct(
        string $accountHash,
        string $shopId,
        string $warehouseId
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->warehouseId = $warehouseId;
    }

    public function getCommand(): array
    {
        return ['warehouse', 'get', 'deliveryOptions'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'warehouse_id' => $this->warehouseId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        $data = $response->getResponseData();

        $options = [];
        foreach ($data['options'] as $deliveryOptionRawData) {
            $dimensionLimit = new DimensionLimit(
                (int)$deliveryOptionRawData['dimension_limit']['max_height'],
                (int)$deliveryOptionRawData['dimension_limit']['max_length'],
                (int)$deliveryOptionRawData['dimension_limit']['max_width'],
                $deliveryOptionRawData['dimension_limit']['unit']
            );

            $weightLimit = new WeightLimit(
                (int)$deliveryOptionRawData['weight_limit']['max_weight'],
                (int)$deliveryOptionRawData['weight_limit']['min_weight'],
                $deliveryOptionRawData['weight_limit']['unit']
            );

            $options[] = new DeliveryOption(
                $deliveryOptionRawData['id'],
                $deliveryOptionRawData['name'],
                $deliveryOptionRawData['type'],
                $deliveryOptionRawData['description'],
                $dimensionLimit,
                $weightLimit
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\Response(
            $options,
            $response->getMessageCollection()
        );
    }
}
