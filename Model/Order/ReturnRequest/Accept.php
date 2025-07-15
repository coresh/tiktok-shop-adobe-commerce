<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\ReturnRequest;

class Accept
{
    private \M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\Accept\Processor $acceptProcessor;
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\Accept\Processor $acceptProcessor,
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->acceptProcessor = $acceptProcessor;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function process(\M2E\TikTokShop\Model\Order $order, int $initiator): bool
    {
        $notSuccessMessages = $this->acceptProcessor->process($order);
        $order->getLogService()->setInitiator($initiator);

        if (empty($notSuccessMessages)) {
            $order->addSuccessLog('Order return request approved by seller; awaiting buyer shipment.');
            $this->updateReturnStatus($order);
        }

        foreach ($notSuccessMessages as $message) {
            if ($message->isError()) {
                $order->addErrorLog(
                    'Order return request was not approved. Reason: %msg%',
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
            $item->setRefundReturnStatus(\M2E\TikTokShop\Model\Order\ReturnRequest\Status::BUYER_AWAITING_BUYER_SHIP);
            $this->orderItemRepository->save($item);
        }
    }
}
