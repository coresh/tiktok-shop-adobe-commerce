<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync;

class AccountLockManager
{
    private const PREFIX_LOCK_NICK = 'synchronization_listing_inventory_for_shop_';
    private const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 3600; // 60 min

    private \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory
    ) {
        $this->lockItemManagerFactory = $lockItemManagerFactory;
    }

    public function isExistByShop(\M2E\TikTokShop\Model\Shop $shop): bool
    {
        $lockManager = $this->getLockManager($shop);

        if ($lockManager->isExist() === false) {
            return false;
        }

        if ($lockManager->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $lockManager->remove();

            return false;
        }

        return true;
    }

    public function isExistByAccount(\M2E\TikTokShop\Model\Account $account): bool
    {
        foreach ($account->getShops() as $shop) {
            if ($this->isExistByShop($shop)) {
                return true;
            }
        }

        return false;
    }

    public function create(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $lockManager = $this->getLockManager($shop);
        $lockManager->create();
    }

    public function remove($shop): void
    {
        $lockManager = $this->getLockManager($shop);
        $lockManager->remove();
    }

    private function getLockManager(\M2E\TikTokShop\Model\Shop $shop): \M2E\TikTokShop\Model\Lock\Item\Manager
    {
        return $this->lockItemManagerFactory->create($this->makeLockNick($shop->getId()));
    }

    private function makeLockNick(int $shopId): string
    {
        return self::PREFIX_LOCK_NICK . $shopId;
    }
}
