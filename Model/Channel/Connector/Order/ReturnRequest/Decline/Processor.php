<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\Decline;

use M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order;
use M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order\Item;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param \M2E\TikTokShop\Model\Order $order
     * @param \M2E\TikTokShop\Model\Order\ReturnRequest\Decline\DeclineReasonCollection $declineReasons
     *
     * @return \M2E\Core\Model\Connector\Response\Message[] not success messages
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Order\Exception\UnableReturn
     */
    public function process(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Model\Order\ReturnRequest\Decline\DeclineReasonCollection $declineReasons
    ): array {
        $requestOrder = new Order($order->getTtsOrderId());
        foreach ($order->getItems() as $item) {
            if (!$item->isReturnRequestedProcessPossible()) {
                continue;
            }

            if (!$item->hasRefundReturnId()) {
                continue;
            }

            $refundReturnId = $item->getRefundReturnId();
            $declineReason = $declineReasons->getReasonByReturnId($refundReturnId);

            if ($declineReason === null) {
                continue;
            }

            $requestOrderItem = new Item($item->getItemId());
            $requestOrderItem->setRefundReturnId($refundReturnId);
            $requestOrderItem->setReason($declineReason->getReason());

            $requestOrder->addOrderItem($requestOrderItem);
        }

        if (empty($requestOrder->getOrderItems())) {
            throw new \M2E\TikTokShop\Model\Order\Exception\UnableReturn(
                (string)__('Unable return order. Order items not valid.'),
            );
        }

        $command = new Command(
            $order->getAccount()->getServerHash(),
            $order->getShop()->getShopId(),
            $requestOrder
        );

        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->singleClient->process($command);

        return array_merge(
            $response->getMessageCollection()->getErrors(),
            $response->getMessageCollection()->getWarnings(),
        );
    }
}
