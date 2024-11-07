<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Servicing\Task;

class License implements \M2E\TikTokShop\Model\Servicing\TaskInterface
{
    public const NAME = 'license';

    private \M2E\TikTokShop\Model\Config\Manager $configManager;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $configManager
    ) {
        $this->configManager = $configManager;
    }

    // ----------------------------------------

    public function getServerTaskName(): string
    {
        return self::NAME;
    }

    // ----------------------------------------

    public function isAllowed(): bool
    {
        return true;
    }

    // ----------------------------------------

    public function getRequestData(): array
    {
        return [];
    }

    // ----------------------------------------

    public function processResponseData(array $data): void
    {
        if (isset($data['info']) && is_array($data['info'])) {
            $this->updateInfoData($data['info']);
        }

        if (isset($data['validation']) && is_array($data['validation'])) {
            $this->updateValidationMainData($data['validation']);

            if (isset($data['validation']['validation']) && is_array($data['validation']['validation'])) {
                $this->updateValidationValidData($data['validation']['validation']);
            }
        }

        if (isset($data['connection']) && is_array($data['connection'])) {
            $this->updateConnectionData($data['connection']);
        }
    }

    // ----------------------------------------

    private function updateInfoData(array $infoData): void
    {
        if (array_key_exists('email', $infoData)) {
            $this->configManager->setGroupValue('/license/info/', 'email', $infoData['email']);
        }
    }

    private function updateValidationMainData(array $validationData): void
    {
        if (array_key_exists('domain', $validationData)) {
            $this->configManager->setGroupValue('/license/domain/', 'valid', $validationData['domain']);
        }

        if (array_key_exists('ip', $validationData)) {
            $this->configManager->setGroupValue('/license/ip/', 'valid', $validationData['ip']);
        }
    }

    private function updateValidationValidData(array $isValidData): void
    {
        if (isset($isValidData['domain'])) {
            $this->configManager->setGroupValue('/license/domain/', 'is_valid', (int)$isValidData['domain']);
        }

        if (isset($isValidData['ip'])) {
            $this->configManager->setGroupValue('/license/ip/', 'is_valid', (int)$isValidData['ip']);
        }
    }

    private function updateConnectionData(array $data): void
    {
        if (array_key_exists('domain', $data)) {
            $this->configManager->setGroupValue('/license/domain/', 'real', $data['domain']);
        }

        if (array_key_exists('ip', $data)) {
            $this->configManager->setGroupValue('/license/ip/', 'real', $data['ip']);
        }
    }
}
