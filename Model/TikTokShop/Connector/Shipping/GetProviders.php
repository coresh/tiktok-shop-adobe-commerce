<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Shipping;

use M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders\ShippingProvider;

class GetProviders implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $deliveryOptionId;
    private string $accountHash;
    private string $shopId;

    public function __construct(
        string $accountHash,
        string $shopId,
        string $deliveryOptionId
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->deliveryOptionId = $deliveryOptionId;
    }

    public function getCommand(): array
    {
        return ['shipping', 'get', 'providers'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'delivery_option_id' => $this->deliveryOptionId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        $data = $response->getResponseData();

        $shippingProviders = [];
        foreach ($data['providers'] as $shippingProviderData) {
            $shippingProviders[] = new ShippingProvider(
                $shippingProviderData['id'],
                $shippingProviderData['name'],
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders\Response(
            $shippingProviders,
            $response->getMessageCollection()
        );
    }
}
