<?php

namespace M2E\TikTokShop\Helper\Data\Cache;

class Permanent implements \M2E\TikTokShop\Helper\Data\Cache\BaseInterface
{
    private \Magento\Framework\App\CacheInterface $cache;

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->cache = $cache;
    }

    // ----------------------------------------

    /**
     * @inheritDoc
     */
    public function getValue($key)
    {
        $cacheKey = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $key;
        $value = $this->cache->load($cacheKey);

        $saveValue = (array)json_decode((string)$value, true);
        if (!isset($saveValue['value'])) {
            return null;
        }

        return $saveValue['value'];
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value, array $tags = [], $lifetime = null): void
    {
        if ($value === null) {
            throw new \M2E\TikTokShop\Model\Exception('Can\'t store NULL value');
        }

        if (is_object($value)) {
            throw new \M2E\TikTokShop\Model\Exception('Can\'t store a php object');
        }

        if ($lifetime === null || (int)$lifetime <= 0) {
            $lifetime = 60 * 60 * 24;
        }

        $cacheKey = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $key;

        $preparedTags = [\M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_main'];
        foreach ($tags as $tag) {
            $preparedTags[] = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $tag;
        }

        $saveValue = ['value' => $value];

        $this->cache->save(
            json_encode($saveValue, JSON_THROW_ON_ERROR),
            $cacheKey,
            $preparedTags,
            (int)$lifetime,
        );
    }

    // ----------------------------------------

    /**
     * @inheritDoc
     */
    public function removeValue($key): void
    {
        $cacheKey = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $key;
        $this->cache->remove($cacheKey);
    }

    /**
     * @inheritDoc
     */
    public function removeTagValues($tag): void
    {
        $tags = [\M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $tag];
        $this->cache->clean($tags);
    }

    /**
     * @inheritDoc
     */
    public function removeAllValues(): void
    {
        $this->removeTagValues('main');
    }
}
