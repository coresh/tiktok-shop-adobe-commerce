<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct;

class Reset
{
    private DeleteService $deleteService;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        DeleteService $deleteService
    ) {
        $this->shopRepository = $shopRepository;
        $this->deleteService = $deleteService;
    }

    public function process(\M2E\TikTokShop\Model\Account $account): void
    {
        $this->deleteService->deleteUnmanagedByAccountId($account->getId());
        $this->resetShopsInventoryLastSyncDate($account);
    }

    private function resetShopsInventoryLastSyncDate(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            $shop->resetInventoryLastSyncDate();
            $this->shopRepository->save($shop);
        }
    }
}
