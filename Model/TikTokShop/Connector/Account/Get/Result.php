<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Get;

class Result
{
    private array $data = [];

    public function isValidAccount(string $hash): bool
    {
        return $this->data[$hash] ?? false;
    }

    public function addAccount(string $hash, bool $isValid): void
    {
        $this->data[$hash] = $isValid;
    }
}
