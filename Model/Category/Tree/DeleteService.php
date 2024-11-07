<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Tree;

class DeleteService
{
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $lastSyncDateService;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $lastSyncDateService
    ) {
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->lastSyncDateService = $lastSyncDateService;
    }

    public function deleteByShop(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $this->lastSyncDateService->delete($shop);
        $this->categoryTreeRepository->deleteByShopId($shop->getId());
    }
}
