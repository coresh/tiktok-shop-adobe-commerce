<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\ReturnRequest\Decline;

class DeclineReasonCollection
{
    /**
     * @var DeclineReason[]
     */
    private array $reasons;

    public function __construct(array $reasons = [])
    {
        $this->reasons = $reasons;
    }

    public function getReasonByReturnId(string $returnId): ?DeclineReason
    {
        foreach ($this->reasons as $reason) {
            if ($reason->getReturnId() === $returnId) {
                return $reason;
            }
        }
        return null;
    }
}
