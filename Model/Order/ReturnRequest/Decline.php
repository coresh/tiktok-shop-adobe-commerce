<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\ReturnRequest;

class Decline
{
    private \M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\Decline\Processor $declineProcessor;
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\Decline\Processor $declineProcessor,
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->declineProcessor = $declineProcessor;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function process(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Model\Order\ReturnRequest\Decline\DeclineReasonCollection $declineReasons,
        int $initiator
    ): bool {
        $notSuccessMessages = $this->declineProcessor->process($order, $declineReasons);
        $order->getLogService()->setInitiator($initiator);

        if (empty($notSuccessMessages)) {
            $order->addSuccessLog("Buyer's return request was declined.");
            $this->updateReturnStatus($order);
        }

        foreach ($notSuccessMessages as $message) {
            if ($message->isError()) {
                $order->addErrorLog(
                    "Buyer's return request was not declined. Reason: %msg%",
                    ['msg' => $message->getText()],
                );
            } else {
                $order->addWarningLog($message->getText());
            }
        }

        return empty($notSuccessMessages);
    }

    private function updateReturnStatus(\M2E\TikTokShop\Model\Order $order): void
    {
        foreach ($order->getItems() as $item) {
            if (!$item->isBuyerRequestReturn()) {
                continue;
            }

            $item->setRefundReturnStatus(
                \M2E\TikTokShop\Model\Order\ReturnRequest\Status::REFUND_OR_RETURN_REQUEST_REJECT
            );
            $this->orderItemRepository->save($item);
        }
    }
}
