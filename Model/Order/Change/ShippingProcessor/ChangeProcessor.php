<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Change\ShippingProcessor;

class ChangeProcessor
{
    private array $shippingProvidersNamesById = [];
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Processor $sendEntityProcessor;
    private \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Processor $sendEntityProcessor,
        \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository
    ) {
        $this->sendEntityProcessor = $sendEntityProcessor;
        $this->shippingProviderRepository = $shippingProviderRepository;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Order\Change $change
    ): ChangeResult {
        $order = $change->getOrder();

        $changeParams = $change->getParams(); // \M2E\TikTokShop\Model\Order\ShipmentService

        $trackingNumber = $changeParams['tracking_number'];
        $shippingProviderId = $changeParams['shipping_provider_id'];

        $orderBuilder = \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\OrderBuilder::create();
        $orderBuilder->setOrderId($order->getTtsOrderId());

        $orderItems = [];
        foreach ($changeParams['items'] as $orderItemData) {
            $itemId = (int)$orderItemData['item_id'];
            $orderItem = $order->findItem($itemId);

            if ($orderItem === null) {
                continue;
            }

            if (!$orderItem->isReadyToShip()) {
                continue;
            }

            $orderItems[] = $orderItem;

            $orderBuilder->addOrderItem($orderItem->getItemId(), $orderItem->getPackageId());
        }

        if ($orderBuilder->getItemsCount() === 0) {
            return ChangeResult::createSkipped();
        }

        $response = $this->sendEntityProcessor->process(
            $account,
            $order->getShop(),
            $shippingProviderId,
            $trackingNumber,
            $orderBuilder->build()
        );

        if (!$response->isSuccess()) {
            return ChangeResult::createFailed(
                $orderItems,
                $trackingNumber,
                $this->getShippingProviderName($shippingProviderId),
                $response->getErrorMessages()
            );
        }

        return ChangeResult::createSuccess(
            $orderItems,
            $response->getPackageId(),
            $trackingNumber,
            $this->getShippingProviderName($shippingProviderId),
            $response->getErrorMessages(),
        );
    }

    private function getShippingProviderName(string $id): string
    {
        if (isset($this->shippingProvidersNamesById[$id])) {
            return $this->shippingProvidersNamesById[$id];
        }

        $provider = $this->shippingProviderRepository->findByShippingProviderId($id);
        $providerName = '';
        if ($provider !== null) {
            $providerName = $provider->getShippingProviderName();
        }

        return $this->shippingProvidersNamesById[$id] = $providerName;
    }
}
