<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Order\ReturnRequest;

class DeclineReason
{
    private string $name;
    private string $text;

    public function __construct(
        string $name,
        string $text
    ) {
        $this->name = $name;
        $this->text = $text;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
