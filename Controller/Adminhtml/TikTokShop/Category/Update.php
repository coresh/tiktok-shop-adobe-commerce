<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class Update extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\Dictionary\UpdateService $updateService;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $repository;
    private \M2E\TikTokShop\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\UpdateService $updateService,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $repository,
        \M2E\TikTokShop\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService
    ) {
        parent::__construct();

        $this->updateService = $updateService;
        $this->repository = $repository;
        $this->categoryTreeSynchronizeService = $categoryTreeSynchronizeService;
    }

    public function execute()
    {
        try {
            $uniqueShops = [];

            foreach ($this->repository->getAllItems() as $category) {
                $this->updateService->update($category);
                $uniqueShops[$category->getShopId()] = $category->getShop();
            }

            foreach ($uniqueShops as $shop) {
                $this->categoryTreeSynchronizeService->synchronize($shop);
            }

            $this->messageManager->addSuccessMessage(__(
                'Category data has been updated.',
            ));
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(__(
                'Category data failed to be updated, please try again.',
            ));
        }

        return $this->_redirect('*/tiktokshop_template_category/index');
    }
}
