<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param \M2E\TikTokShop\Model\Order $order
     * @param string $reason
     *
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel\Response
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\Core\Model\Exception\Connection\SystemError
     * @throws \M2E\TikTokShop\Model\Order\Exception\UnableCancel
     */
    public function process(\M2E\TikTokShop\Model\Order $order, string $reason): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel\Response
    {
        $requestOrder = new Order($order->getTtsOrderId());
        foreach ($order->getItems() as $item) {
            $requestOrder->addOrderLineItemId($item->getItemId());
            $requestOrder->addSku($item->getChannelSkuId(), $item->getQtyPurchased());
        }

        if (
            empty($requestOrder->getSkusData())
            || empty($requestOrder->getOrderLineItemIds())
        ) {
            throw new \M2E\TikTokShop\Model\Order\Exception\UnableCancel(
                (string)__('Unable cancel order. Order items not valid.'),
            );
        }

        $command = new EntityCommand(
            $order->getAccount()->getServerHash(),
            $order->getShop()->getShopId(),
            $requestOrder,
            $reason,
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel\Response $response */
        return $this->singleClient->process($command);
    }
}
