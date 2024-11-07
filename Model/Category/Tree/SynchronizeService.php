<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Tree;

use M2E\TikTokShop\Model\TikTokShop\Connector\Category\Category as ResponseCategory;
use M2E\TikTokShop\Model\Category\Tree;

class SynchronizeService
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Category\Get\Processor $connectionProcessor;
    private \M2E\TikTokShop\Model\Category\Tree\DeleteService $categoryTreeDeleteService;
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\TikTokShop\Model\Category\TreeFactory $categoryFactory;
    private \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $lastSyncDateService;

    private static array $permissionStatusesMap = [
        ResponseCategory::PERMISSION_STATUSES_AVAILABLE => Tree::PERMISSION_STATUSES_AVAILABLE,
        ResponseCategory::PERMISSION_STATUSES_INVITE_ONLY => Tree::PERMISSION_STATUSES_INVITE_ONLY,
        ResponseCategory::PERMISSION_STATUSES_NON_MAIN_CATEGORY => Tree::PERMISSION_STATUSES_NON_MAIN_CATEGORY,
    ];

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Category\Get\Processor $connectionProcessor,
        \M2E\TikTokShop\Model\Category\Tree\DeleteService $categoryTreeDeleteService,
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\TikTokShop\Model\Category\TreeFactory $categoryFactory,
        \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $lastSyncDateService
    ) {
        $this->connectionProcessor = $connectionProcessor;
        $this->categoryTreeDeleteService = $categoryTreeDeleteService;
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->categoryFactory = $categoryFactory;
        $this->lastSyncDateService = $lastSyncDateService;
    }

    public function synchronize(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $response = $this->connectionProcessor->process($shop->getAccount(), $shop);

        $categories = [];
        foreach ($response->getCategories() as $category) {
            $categories[] = $this->categoryFactory->create()->create(
                $shop->getId(),
                $category->getId(),
                $category->getParentId(),
                $category->getName(),
                $category->isLeaf(),
                $this->getMappedPermissionStatuses($category)
            );
        }

        $this->categoryTreeDeleteService->deleteByShop($shop);
        $this->categoryTreeRepository->batchInsert($categories);

        $this->lastSyncDateService->touch($shop);
    }

    /**
     * @return string[]
     */
    private function getMappedPermissionStatuses(
        ResponseCategory $responseCategory
    ): array {
        $statuses = [];
        foreach ($responseCategory->getPermissionStatuses() as $status) {
            $statuses[] = self::$permissionStatusesMap[$status];
        }

        return $statuses;
    }
}
