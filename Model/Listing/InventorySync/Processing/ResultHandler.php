<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Processing;

class ResultHandler implements \M2E\TikTokShop\Model\Processing\PartialResultHandlerInterface
{
    public const NICK = 'listing_inventory_sync';

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\UnmanagedProduct\UpdaterFactory $listingUnmanagedUpdaterFactory;
    private \M2E\TikTokShop\Model\Listing\InventorySync\AccountLockManager $accountLockManager;
    private \M2E\TikTokShop\Model\Listing\InventorySync\ProductBuilderFactory $unmanagedBuilderFactory;
    private \M2E\TikTokShop\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor;
    private \DateTime $fromDate;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\UpdaterFactory $listingUnmanagedUpdaterFactory,
        \M2E\TikTokShop\Model\Listing\InventorySync\AccountLockManager $accountLockManager,
        \M2E\TikTokShop\Model\Listing\InventorySync\ProductBuilderFactory $unmanagedBuilderFactory,
        \M2E\TikTokShop\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->listingUnmanagedUpdaterFactory = $listingUnmanagedUpdaterFactory;
        $this->accountLockManager = $accountLockManager;
        $this->unmanagedBuilderFactory = $unmanagedBuilderFactory;
        $this->productUpdateFromChannelProcessor = $productUpdateFromChannelProcessor;
        $this->shopRepository = $shopRepository;
    }

    public function initialize(array $params): void
    {
        if (!isset($params['account_id'], $params['shop_id'])) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Processing params is not valid.');
        }

        $account = $this->accountRepository->find($params['account_id']);
        if ($account === null) {
            throw new \M2E\TikTokShop\Model\Exception('Account not found');
        }

        $this->account = $account;
        $shop = null;
        foreach ($this->account->getShops() as $accountShop) {
            if ($accountShop->getId() === $params['shop_id']) {
                $shop = $accountShop;
                break;
            }
        }

        if ($shop === null) {
            throw new \M2E\TikTokShop\Model\Exception('Shop not found');
        }

        $this->shop = $shop;

        if (isset($params['current_date'])) {
            $this->fromDate = \M2E\TikTokShop\Helper\Date::createDateGmt($params['current_date']);
        }
    }

    public function processPartialResult(array $partialData): void
    {
        $unmanagedBuilder = $this->unmanagedBuilderFactory->create($this->account, $this->shop);
        $itemsCollection = $unmanagedBuilder->build($partialData);

        $existInListingCollection = $this->listingUnmanagedUpdaterFactory
            ->create($this->account, $this->shop)
            ->process(clone $itemsCollection);
        if ($existInListingCollection === null) {
            return;
        }

        $this->productUpdateFromChannelProcessor
            ->process($existInListingCollection, $this->account, $this->shop);
    }

    public function processSuccess(array $resultData, array $messages): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->fromDate)) {
            $this->shop->setInventoryLastSyncDate(clone $this->fromDate);

            $this->shopRepository->save($this->shop);
        }
    }

    public function processExpire(): void
    {
        // do nothing
    }

    public function clearLock(\M2E\TikTokShop\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->delete(\M2E\TikTokShop\Model\Shop::LOCK_NICK, $this->shop->getId());
        $this->accountLockManager->remove($this->shop);
    }
}
