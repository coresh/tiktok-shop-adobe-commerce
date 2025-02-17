<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Data\Cache;

class Runtime implements \M2E\TikTokShop\Helper\Data\Cache\BaseInterface
{
    private array $cacheStorage = [];

    public function getValue(string $key)
    {
        return $this->cacheStorage[$key]['data'] ?? null;
    }

    public function setValue(string $key, $value, array $tags = [], ?int $lifetime = null): void
    {
        $this->cacheStorage[$key] = [
            'data' => $value,
            'tags' => $tags,
        ];
    }

    // ----------------------------------------

    public function removeValue(string $key): void
    {
        unset($this->cacheStorage[$key]);
    }

    public function removeTagValues(string $tag): void
    {
        foreach ($this->cacheStorage as $key => $data) {
            if (!in_array($tag, $data['tags'])) {
                continue;
            }

            unset($this->cacheStorage[$key]);
        }
    }

    public function removeAllValues(): void
    {
        $this->cacheStorage = [];
    }
}
