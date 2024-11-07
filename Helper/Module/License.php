<?php

namespace M2E\TikTokShop\Helper\Module;

class License
{
    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config
    ) {
        $this->config = $config;
    }

    public function getKey()
    {
        return (string)$this->config->getGroupValue('/license/', 'key');
    }

    public function getDomain()
    {
        return (string)$this->config->getGroupValue('/license/domain/', 'valid');
    }

    public function getIp()
    {
        return (string)$this->config->getGroupValue('/license/ip/', 'valid');
    }

    public function getEmail()
    {
        return (string)$this->config->getGroupValue('/license/info/', 'email');
    }

    public function isValidDomain()
    {
        $isValid = $this->config->getGroupValue('/license/domain/', 'is_valid');

        return $isValid === null || $isValid;
    }

    public function isValidIp()
    {
        $isValid = $this->config->getGroupValue('/license/ip/', 'is_valid');

        return $isValid === null || (bool)$isValid;
    }

    public function getRealDomain()
    {
        return (string)$this->config->getGroupValue('/license/domain/', 'real');
    }

    public function getRealIp()
    {
        return (string)$this->config->getGroupValue('/license/ip/', 'real');
    }

    public function setLicenseKey(string $key): void
    {
        $this->config->setGroupValue('/license/', 'key', $key);
    }

    public function getData(): array
    {
        return [
            'key' => $this->getKey(),
            'domain' => $this->getDomain(),
            'ip' => $this->getIp(),
            'info' => [
                'email' => $this->getEmail(),
            ],
            'valid' => [
                'domain' => $this->isValidDomain(),
                'ip' => $this->isValidIp(),
            ],
            'connection' => [
                'domain' => $this->getRealDomain(),
                'ip' => $this->getRealIp(),
            ],
        ];
    }
}
