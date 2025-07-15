<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\ReturnRequest;

class DeclineReasons
{
    private \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReasons\Retriever $declineReasonRetriever;

    public function __construct(
        \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReasons\Retriever $declineReasonRetriever
    ) {
        $this->declineReasonRetriever = $declineReasonRetriever;
    }

    /**
     * @param \M2E\TikTokShop\Model\Order $order
     *
     * @return \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReason[]
     */
    public function getDeclineReasonsByReturnId(\M2E\TikTokShop\Model\Order $order): array
    {
        $declineReasons = [];
        foreach ($order->getItems() as $item) {
            if ($item->isReturnRequestedProcessPossible()) {
                $response = $this->declineReasonRetriever->process($item);
                $declineReasons[$item->getRefundReturnId()] = $response->getReasons();
            }
        }

        return $declineReasons;
    }
}
