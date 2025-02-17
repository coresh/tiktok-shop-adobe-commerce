<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Data\Cache;

class Permanent implements \M2E\TikTokShop\Helper\Data\Cache\BaseInterface
{
    private \M2E\Core\Model\Cache\Adapter $adapter;

    public function __construct(\M2E\Core\Model\Cache\AdapterFactory $cacheAdapterFactory)
    {
        $this->adapter = $cacheAdapterFactory->create(\M2E\TikTokShop\Helper\Module::IDENTIFIER);
    }

    public function getValue(string $key)
    {
        return $this->adapter->get($key);
    }

    public function setValue(string $key, $value, array $tags = [], ?int $lifetime = null): void
    {
        if ($lifetime === null || $lifetime <= 0) {
            $lifetime = 60 * 60 * 24;
        }

        $this->adapter->set($key, $value, $lifetime, $tags);
    }

    public function removeValue(string $key): void
    {
        $this->adapter->remove($key);
    }

    public function removeTagValues(string $tag): void
    {
        $this->adapter->removeByTag($tag);
    }

    public function removeAllValues(): void
    {
        $this->adapter->removeAllValues();
    }

    public function getAdapter(): \M2E\Core\Model\Cache\Adapter
    {
        return $this->adapter;
    }
}
