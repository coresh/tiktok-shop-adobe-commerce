<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Get;

class Status
{
    private ?string $note;
    private bool $isActive;

    public function __construct(
        bool $isActive,
        ?string $note
    ) {
        $this->isActive = $isActive;
        $this->note = $note;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }
}
