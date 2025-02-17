<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector\Client;

class Config implements \M2E\Core\Model\Connector\Client\ConfigInterface
{
    private const CONFIG_GROUP_SERVER = '/server/';
    private const CONFIG_KEY_APPLICATION_KEY = 'application_key';

    private \M2E\TikTokShop\Model\Config\Manager $config;
    private \M2E\Core\Model\Connector\Client\ConfigManager $connectorConfig;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\Core\Model\Connector\Client\ConfigManager $connectorConfig,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        $this->config = $config;
        $this->connectorConfig = $connectorConfig;
        $this->licenseService = $licenseService;
    }

    public function getHost(): string
    {
        return $this->connectorConfig->getHost();
    }

    public function getConnectionTimeout(): int
    {
        return 15;
    }

    public function getTimeout(): int
    {
        return 300;
    }

    public function getApplicationKey(): string
    {
        return (string)$this->config->getGroupValue(self::CONFIG_GROUP_SERVER, self::CONFIG_KEY_APPLICATION_KEY);
    }

    public function getLicenseKey(): ?string
    {
        $license = $this->licenseService->get();

        return $license->hasKey() ? $license->getKey() : null;
    }
}
