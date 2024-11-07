<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Log;

class Record
{
    private string $message;
    private int $type;

    public function __construct(
        string $message,
        int $type
    ) {
        $this->message = $message;
        $this->type = $type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): int
    {
        return $this->type;
    }
}
