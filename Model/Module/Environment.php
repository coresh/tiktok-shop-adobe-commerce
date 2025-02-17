<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Module;

class Environment implements \M2E\Core\Model\Module\EnvironmentInterface
{
    private \M2E\Core\Model\Module\Environment\Adapter $adapter;
    private \M2E\Core\Model\Module\Environment\AdapterFactory $adapterFactory;
    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(
        \M2E\Core\Model\Module\Environment\AdapterFactory $adapterFactory,
        \M2E\TikTokShop\Model\Config\Manager $config
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->config = $config;
    }

    public function isProductionEnvironment(): bool
    {
        return $this->getAdapter()->isProductionEnvironment();
    }

    public function isDevelopmentEnvironment(): bool
    {
        return $this->getAdapter()->isDevelopmentEnvironment();
    }

    public function enableProductionEnvironment(): void
    {
        $this->getAdapter()->enableProductionEnvironment();
    }

    public function enableDevelopmentEnvironment(): void
    {
        $this->getAdapter()->enableDevelopmentEnvironment();
    }

    public function getAdapter(): \M2E\Core\Model\Module\Environment\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory->create($this->config->getAdapter());
        }

        return $this->adapter;
    }
}
