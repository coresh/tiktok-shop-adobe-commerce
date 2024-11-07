<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest;

class Order
{
    private string $ttsOrderStatus;
    private bool $isBuyerRequestedCancel;
    private string $cancelReason;

    public function __construct(
        string $ttsOrderStatus,
        bool $isBuyerRequestCancel,
        string $cancelReason
    ) {
        $this->ttsOrderStatus = $ttsOrderStatus;
        $this->isBuyerRequestedCancel = $isBuyerRequestCancel;
        $this->cancelReason = $cancelReason;
    }

    public function getTtsOrderStatus(): string
    {
        return $this->ttsOrderStatus;
    }

    public function isBuyerRequestedCancel(): bool
    {
        return $this->isBuyerRequestedCancel;
    }

    public function getCancelReason(): string
    {
        return $this->cancelReason;
    }
}
