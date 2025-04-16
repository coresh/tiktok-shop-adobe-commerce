<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Tree;

class LastSyncDateService
{
    private \M2E\TikTokShop\Model\Registry\Manager $registry;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registry
    ) {
        $this->registry = $registry;
    }

    public function get(\M2E\TikTokShop\Model\Shop $shop): ?\DateTime
    {
        $date = $this->registry->getValue($this->prepareRegistryKey($shop));

        if ($date === null) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($date);
    }

    public function touch(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $this->registry->setValue(
            $this->prepareRegistryKey($shop),
            \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );
    }

    public function delete(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $this->registry->deleteValue($this->prepareRegistryKey($shop));
    }

    public function isNeedSynchronizeCategoryTree(\M2E\TikTokShop\Model\Shop $shop): bool
    {
        $lastSyncDate = $this->get($shop);
        if ($lastSyncDate === null) {
            return true;
        }

        return false;
    }

    private function prepareRegistryKey(\M2E\TikTokShop\Model\Shop $shop): string
    {
        return sprintf('/category/tree/last_sync_date/tts_shop_id/%d', $shop->getShopId());
    }
}
