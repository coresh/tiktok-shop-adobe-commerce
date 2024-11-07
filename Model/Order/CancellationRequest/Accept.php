<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\CancellationRequest;

class Accept
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Accept\Processor $acceptProcessor;
    private \M2E\TikTokShop\Model\TikTokShop\Order\StatusResolver $orderStatusHelper;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\Order\CreditMemo $orderCreditMemo;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Accept\Processor $acceptProcessor,
        \M2E\TikTokShop\Model\TikTokShop\Order\StatusResolver                                 $orderStatusHelper,
        \M2E\TikTokShop\Model\Order\Repository                                                $orderRepository,
        \M2E\TikTokShop\Model\Order\CreditMemo                                                $orderCreditMemo
    ) {
        $this->acceptProcessor = $acceptProcessor;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->orderRepository = $orderRepository;
        $this->orderCreditMemo = $orderCreditMemo;
    }

    public function process(\M2E\TikTokShop\Model\Order $order, int $initiator): bool
    {
        if (!$order->isBuyerCancellationRequest()) {
            return true;
        }

        $acceptResponse = $this->acceptProcessor->process($order);
        if ($acceptResponse->hasOrder()) {
            $this->updateOrderData($order, $acceptResponse->getOrder());
        }

        $this->writeOrderLogs($order, $initiator, $acceptResponse);

        if (!$acceptResponse->hasErrors()) {
            $this->cancelMagentoOrder($order);
            $this->cancelReservation($order);
        }

        return !$acceptResponse->hasErrors();
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
                    'Order Cancellation Request was not accepted. Reason: %msg%',
                    ['msg' => $message->getText()]
                );
            }
        } else {
            $order->addSuccessLog('Order Cancellation Request was accepted.');
        }
    }

    private function cancelReservation(\M2E\TikTokShop\Model\Order $order): void
    {
        if ($order->getReserve()->isPlaced()) {
            $order->getReserve()->release();
        }
    }

    private function cancelMagentoOrder(\M2E\TikTokShop\Model\Order $order): void
    {
        $this->orderCreditMemo->process($order);
    }
}
