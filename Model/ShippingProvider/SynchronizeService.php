<?php

namespace M2E\TikTokShop\Model\ShippingProvider;

class SynchronizeService
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;
    private \M2E\TikTokShop\Model\ShippingProviderFactory $shippingProviderFactory;
    private \M2E\TikTokShop\Model\ResourceModel\ShippingProvider $shippingProviderResource;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Single $singleClient,
        \M2E\TikTokShop\Model\ShippingProviderFactory $shippingProviderFactory,
        \M2E\TikTokShop\Model\ResourceModel\ShippingProvider $shippingProviderResource
    ) {
        $this->singleClient = $singleClient;
        $this->shippingProviderFactory = $shippingProviderFactory;
        $this->shippingProviderResource = $shippingProviderResource;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function synchronizeShippingProviders(
        \M2E\TikTokShop\Model\Warehouse $warehouse
    ): void {
        $shop = $warehouse->getShop();
        $account = $shop->getAccount();

        $deliveryOptions = $this->receiveDeliveryOptions($account, $shop, $warehouse);

        foreach ($deliveryOptions->getDeliveryOptions() as $deliveryOption) {
            $shippingProviders = $this->receiveShippingProviders(
                $account,
                $shop,
                $deliveryOption
            );

            foreach ($shippingProviders->getShippingProviders() as $shippingProvider) {
                $entity = $this->shippingProviderFactory->create();
                $entity->create(
                    $account,
                    $shop,
                    $warehouse,
                    $deliveryOption->getId(),
                    $shippingProvider->getId(),
                    $shippingProvider->getName(),
                );

                $this->shippingProviderResource->save($entity);
            }
        }
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     */
    private function receiveDeliveryOptions(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Warehouse $warehouse
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptionsCommand(
            $account->getServerHash(),
            $shop->getShopId(),
            $warehouse->getWarehouseId()
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\Response $response */
        $response = $this->singleClient->process($command);

        return $response;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function receiveShippingProviders(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DeliveryOption $deliveryOption
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders\Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders(
            $account->getServerHash(),
            $shop->getShopId(),
            $deliveryOption->getId()
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders\Response $response */
        $response = $this->singleClient->process($command);

        return $response;
    }
}
