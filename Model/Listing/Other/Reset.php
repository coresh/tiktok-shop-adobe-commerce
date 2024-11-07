<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

class Reset
{
    private Repository $repository;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        Repository $listingOtherRepository
    ) {
        $this->shopRepository = $shopRepository;
        $this->repository = $listingOtherRepository;
        $this->accountRepository = $accountRepository;
    }

    public function process(): void
    {
        foreach ($this->accountRepository->getAll() as $account) {
            $this->removeListingOther($account);
            $this->resetShopsInventoryLastSyncDate($account);
        }
    }

    private function removeListingOther(\M2E\TikTokShop\Model\Account $account): void
    {
        $this->repository->removeByAccountId($account->getId());
    }

    private function resetShopsInventoryLastSyncDate(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            $shop->resetInventoryLastSyncDate();
            $this->shopRepository->save($shop);
        }
    }
}
