<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Account;

use M2E\TikTokShop\Model\ResourceModel\Account as AccountResource;

class Repository
{
    use \M2E\TikTokShop\Model\CacheTrait;

    private \M2E\TikTokShop\Model\ResourceModel\Account\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\AccountFactory $accountFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Account $accountResource;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\AccountFactory $accountFactory,
        \M2E\TikTokShop\Model\ResourceModel\Account $accountResource,
        \M2E\TikTokShop\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->accountFactory = $accountFactory;
        $this->accountResource = $accountResource;
        $this->cache = $cache;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Account
    {
        $account = $this->accountFactory->create();

        $cacheData = $this->cache->getValue($this->makeCacheKey($account, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($account, $cacheData);

            return $account;
        }

        $this->accountResource->load($account, $id);

        if ($account->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($account, $id),
            $this->getCacheDate($account),
            [],
            60 * 60
        );

        return $account;
    }

    public function get(int $id): \M2E\TikTokShop\Model\Account
    {
        $account = $this->find($id);
        if ($account === null) {
            throw new \LogicException("Account '$id' not found.");
        }

        return $account;
    }

    /**
     * @return \M2E\TikTokShop\Model\Account[]
     */
    public function getAll(bool $onlyActive = true): array
    {
        $collection = $this->collectionFactory->create();
        if ($onlyActive) {
            $collection->addFieldToFilter(AccountResource::COLUMN_IS_ACTIVE, 1);
        }

        return array_values($collection->getItems());
    }

    public function getFirst(): \M2E\TikTokShop\Model\Account
    {
        $collection = $this->collectionFactory->create();
        /** @var \M2E\TikTokShop\Model\Account $firstAccount */
        $firstAccount = $collection->getFirstItem();

        if ($firstAccount->isObjectNew()) {
            throw new \LogicException('Not found any accounts');
        }

        return $firstAccount;
    }

    public function findByOpenId(string $openId): ?\M2E\TikTokShop\Model\Account
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(AccountResource::COLUMN_OPEN_ID, $openId);

        /** @var \M2E\TikTokShop\Model\Account $account */
        $account = $collection->getFirstItem();
        if ($account->isObjectNew()) {
            return null;
        }

        return $account;
    }

    /**
     * @return \M2E\TikTokShop\Model\Account[]
     */
    public function findActiveWithEnabledInventorySync(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(AccountResource::COLUMN_IS_ACTIVE, 1);
        $collection->addFieldToFilter(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION, 1);

        return array_values($collection->getItems());
    }

    public function create(\M2E\TikTokShop\Model\Account $account): void
    {
        $this->accountResource->save($account);
    }

    public function save(\M2E\TikTokShop\Model\Account $account): void
    {
        $this->accountResource->save($account);
        $this->cache->removeValue($this->makeCacheKey($account, $account->getId()));
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function remove(\M2E\TikTokShop\Model\Account $account): void
    {
        $this->cache->removeValue($this->makeCacheKey($account, $account->getId()));

        $account->delete();
    }
}
