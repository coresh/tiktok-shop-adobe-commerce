<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Tree;

class PathBuilder
{
    private const CACHE_LIFETIME_ONE_HOUR = 3600;

    private Repository $repository;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        Repository $repository,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function getPath(\M2E\TikTokShop\Model\Category\Tree $treeItem): string
    {
        $title = $this->findInCache($treeItem);
        if ($title !== null) {
            return $title;
        }

        $title = $this->buildTitle($treeItem);
        $this->setToCache($treeItem, $title);

        return $title;
    }

    private function buildTitle(\M2E\TikTokShop\Model\Category\Tree $treeItem): string
    {
        $ancestors = $this->repository->getParents($treeItem);
        if (empty($ancestors)) {
            return $treeItem->getTitle();
        }

        $titles = array_map(static function (\M2E\TikTokShop\Model\Category\Tree $ancestor) {
            return $ancestor->getTitle();
        }, $ancestors);
        $titles[] = $treeItem->getTitle();

        return implode(' > ', $titles);
    }

    private function findInCache(\M2E\TikTokShop\Model\Category\Tree $treeItem): ?string
    {
        $title = $this->cache->getValue($this->createCacheKey($treeItem));
        if (empty($title)) {
            return null;
        }

        return (string)$title;
    }

    private function setToCache(\M2E\TikTokShop\Model\Category\Tree $treeItem, string $title): void
    {
        $this->cache->setValue($this->createCacheKey($treeItem), $title, [], self::CACHE_LIFETIME_ONE_HOUR);
    }

    private function createCacheKey(\M2E\TikTokShop\Model\Category\Tree $treeItem): string
    {
        return 'category.tree.path.' . $treeItem->getId();
    }
}
