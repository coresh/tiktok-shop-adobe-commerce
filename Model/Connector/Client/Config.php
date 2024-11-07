<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector\Client;

class Config
{
    private \M2E\TikTokShop\Model\Config\Manager $config;
    private \M2E\TikTokShop\Helper\Module\License $licenseHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Helper\Module\License $licenseHelper
    ) {
        $this->config = $config;
        $this->licenseHelper = $licenseHelper;
    }

    public function getHost(): string
    {
        return rtrim((string)$this->config->getGroupValue('/server/', 'host'), '/');
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
        return (string)$this->config->getGroupValue('/server/', 'application_key');
    }

    public function getLicenseKey(): ?string
    {
        $key = $this->licenseHelper->getKey();

        return empty($key) ? null : $key;
    }
}
