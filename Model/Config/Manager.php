<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Config;

class Manager
{
    private \M2E\Core\Model\Config\Adapter $adapter;

    private \M2E\Core\Model\Config\AdapterFactory $adapterFactory;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $permanentCache;

    public function __construct(
        \M2E\Core\Model\Config\AdapterFactory $adapterFactory,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $permanentCache
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->permanentCache = $permanentCache;
    }

    public function getGroupValue(string $group, string $key)
    {
        return $this->getAdapter()->get($group, $key);
    }

    public function setGroupValue(string $group, string $key, $value): void
    {
        $this->getAdapter()->set($group, $key, $value);
    }

    public function hasGroupValue(string $group, string $key): bool
    {
        return $this->getAdapter()->has($group, $key);
    }

    public function getAdapter(): \M2E\Core\Model\Config\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory->create(
                \M2E\TikTokShop\Helper\Module::IDENTIFIER,
                $this->permanentCache->getAdapter()
            );
        }

        return $this->adapter;
    }
}
