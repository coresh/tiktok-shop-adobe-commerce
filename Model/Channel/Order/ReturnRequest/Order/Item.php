<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order;

class Item
{
    private string $itemId;
    private string $refundReturnId;

    private ?string $reason = null;

    public function __construct(
        string $itemId
    ) {
        $this->itemId = $itemId;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function setRefundReturnId(string $refundReturnId): void
    {
        $this->refundReturnId = $refundReturnId;
    }

    public function getRefundReturnId(): string
    {
        return $this->refundReturnId;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}
