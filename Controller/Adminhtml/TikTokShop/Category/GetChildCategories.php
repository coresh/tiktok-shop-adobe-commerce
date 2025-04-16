<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class GetChildCategories extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\TikTokShop\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService;
    private \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $categoryTreeLastSyncDateService;

    public function __construct(
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\TikTokShop\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService,
        \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $categoryTreeLastSyncDateService
    ) {
        parent::__construct();

        $this->shopRepository = $shopRepository;
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->categoryTreeSynchronizeService = $categoryTreeSynchronizeService;
        $this->categoryTreeLastSyncDateService = $categoryTreeLastSyncDateService;
    }

    public function execute()
    {
        $shopId = $this->getRequest()->getParam('shop_id');
        $parentCategoryId = $this->getRequest()->getParam('parent_category_id');
        $parentCategoryId = !empty($parentCategoryId) ? $parentCategoryId : null;

        $shop = $this->shopRepository->find((int)$shopId);
        if ($shop === null) {
            $this->setJsonContent(
                [
                    'success' => false,
                    'messages' => [
                        ['error' => 'Invalid shop id'],
                    ],
                ]
            );

            return $this->getResult();
        }

        $categories = $this->getCategories($shop, $parentCategoryId);
        $skipInviteInviteOnly = $shop->getRegion()->isUS();

        $response = [];
        foreach ($categories as $category) {
            $response[] = [
                'category_id' => $category->getCategoryId(),
                'title' => $category->getTitle(),
                'is_leaf' => (int)$category->isLeaf(),
                'invite_only' => (int)$category->isInviteOnly(),
                'skip_invite' => (int)$skipInviteInviteOnly
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }

    private function getCategories(\M2E\TikTokShop\Model\Shop $shop, ?string $categoryId): array
    {
        if ($this->categoryTreeLastSyncDateService->isNeedSynchronizeCategoryTree($shop)) {
            $this->categoryTreeSynchronizeService->synchronize($shop);
        }

        if ($categoryId === null) {
            return $this->categoryTreeRepository->getRootCategories($shop->getId());
        }

        return $this->categoryTreeRepository->getChildCategories($shop->getId(), (int)$categoryId);
    }
}
