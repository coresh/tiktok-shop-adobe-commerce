<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Servicing;

interface TaskInterface
{
    public function getServerTaskName(): string;

    public function isAllowed(): bool;

    public function getRequestData(): array;

    public function processResponseData(array $data): void;
}
