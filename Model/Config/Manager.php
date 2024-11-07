<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Config;

class Manager
{
    private const CACHE_LIFETIME_ONE_HOUR = 3600;

    private \M2E\TikTokShop\Model\ResourceModel\Config\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Config\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->cache = $cache;
    }

    public function getGroupValue(string $group, string $key)
    {
        $group = $this->prepareGroup($group);
        $key = $this->prepareKey($key);
        if (empty($group) || empty($key)) {
            return null;
        }

        $cacheData = $this->getCacheData();
        if (!empty($cacheData)) {
            return $cacheData[$group][$key] ?? null;
        }

        $cacheData = [];
        /** @var \M2E\TikTokShop\Model\Config $item */
        foreach ($this->collectionFactory->create() as $item) {
            $cacheGroup = $this->prepareGroup($item->getGroup());
            $cacheKey = $this->prepareKey($item->getKey());

            if (!isset($cacheData[$cacheGroup])) {
                $cacheData[$cacheGroup] = [];
            }

            $cacheData[$cacheGroup][$cacheKey] = $item->getValue();
        }

        $this->setCacheData($cacheData);

        return $cacheData[$group][$key] ?? null;
    }

    public function setGroupValue(string $group, string $key, $value): bool
    {
        $group = $this->prepareGroup($group);
        $key = $this->prepareKey($key);
        if (empty($key) || empty($group)) {
            return false;
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\TikTokShop\Model\ResourceModel\Config::COLUMN_GROUP, $group);
        $collection->addFieldToFilter(\M2E\TikTokShop\Model\ResourceModel\Config::COLUMN_KEY, $key);

        /** @var \M2E\TikTokShop\Model\Config $item */
        $item = $collection->getFirstItem();

        if ($item->getId()) {
            $item->setValue($value)
                 ->save();
        } else {
            $item->setGroup($group)
                 ->setKey($key)
                 ->setValue($value)
                 ->save();
        }

        $this->removeCacheData();

        return true;
    }

    // ----------------------------------------

    private function getCacheData()
    {
        return $this->cache->getValue('tiktokshop_config_data');
    }

    private function setCacheData(array $data): void
    {
        $this->cache->setValue('tiktokshop_config_data', $data, [], self::CACHE_LIFETIME_ONE_HOUR);
    }

    private function removeCacheData(): void
    {
        $this->cache->removeValue('tiktokshop_config_data');
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function prepareGroup(string $group): string
    {
        if (empty($group)) {
            throw new \M2E\TikTokShop\Model\Exception('Configuration group cannot be empty.');
        }

        $group = trim($group);
        if ($group === '/') {
            return $group;
        }

        return '/' . strtolower(trim($group, '/')) . '/';
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function prepareKey(string $key): string
    {
        if (empty($key)) {
            throw new \M2E\TikTokShop\Model\Exception('Configuration key cannot be empty.');
        }

        return strtolower(trim($key));
    }
}
