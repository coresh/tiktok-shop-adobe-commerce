<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\ReturnRequest\Decline;

class DeclineReason
{
    private string $returnId;
    private string $reason;

    public function __construct(
        string $returnId,
        string $reason
    ) {
        $this->returnId = $returnId;
        $this->reason = $reason;
    }

    public function getReturnId(): string
    {
        return $this->returnId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
