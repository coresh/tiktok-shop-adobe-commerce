<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Servicing\Task;

class Settings implements \M2E\TikTokShop\Model\Servicing\TaskInterface
{
    public const NAME = 'settings';

    private \M2E\TikTokShop\Model\Module $module;

    public function __construct(
        \M2E\TikTokShop\Model\Module $module
    ) {
        $this->module = $module;
    }

    public function getServerTaskName(): string
    {
        return self::NAME;
    }

    public function isAllowed(): bool
    {
        return true;
    }

    public function getRequestData(): array
    {
        return [];
    }

    public function processResponseData(array $data): void
    {
        $this->updateLastVersion($data);
    }

    private function updateLastVersion(array $data): void
    {
        if (empty($data['last_version'])) {
            return;
        }

        $this->module->setLatestVersion((string)$data['last_version']);
    }
}
