<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\CancellationRequest;

class Decline
{
    public const REASON_INVALID_REASON = 'invalid_reason';
    public const REASON_DELIVERY_SCHEDULE = 'delivery_schedule';
    public const REASON_REACHED_AGREEMENT = 'reached_agreement';
    public const REASON_PRODUCT_PACKED = 'product_packed';

    private \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Decline\Processor $declineProcessor;
    private \M2E\TikTokShop\Model\TikTokShop\Order\StatusResolver $orderStatusHelper;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Decline\Processor $declineProcessor,
        \M2E\TikTokShop\Model\TikTokShop\Order\StatusResolver                                  $orderStatusHelper,
        \M2E\TikTokShop\Model\Order\Repository                                                 $orderRepository
    ) {
        $this->declineProcessor = $declineProcessor;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->orderRepository = $orderRepository;
    }

    public function process(\M2E\TikTokShop\Model\Order $order, string $declineReason, int $initiator): bool
    {
        if (!$order->isBuyerCancellationRequest()) {
            return true;
        }

        $declineResponse = $this->declineProcessor->process($order, $declineReason);
        if ($declineResponse->hasOrder()) {
            $this->updateOrderData($order, $declineResponse->getOrder());
        }

        $this->writeOrderLogs($order, $initiator, $declineResponse);

        return !$declineResponse->hasErrors();
    }

    private function updateOrderData(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Order $responseOrder
    ): void {
        $orderStatus = $this->orderStatusHelper
            ->resolve($responseOrder->getTtsOrderStatus());

        $order->setOrderStatus($orderStatus);
        if ($responseOrder->isBuyerRequestedCancel()) {
            $order->buyerWantCancellation($responseOrder->getCancelReason());
        } else {
            $order->buyerDontWantCancellation();
        }

        $this->orderRepository->save($order);
    }

    private function writeOrderLogs(
        \M2E\TikTokShop\Model\Order $order,
        int $initiator,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Response $declineResponse
    ): void {
        $order->getLogService()->setInitiator($initiator);

        if ($declineResponse->hasErrors()) {
            foreach ($declineResponse->getErrorMessages() as $message) {
                $order->addErrorLog(
                    'Order Cancellation Request was not declined. Reason: %msg%',
                    ['msg' => $message->getText()]
                );
            }
        } else {
            $order->addSuccessLog('Order Cancellation Request was declined.');
        }
    }
}
