<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class GetRecent extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    /** @var \M2E\TikTokShop\Model\Category\Dictionary\Repository */
    private $categoryRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository
    ) {
        parent::__construct();

        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $shopId = $this->getRequest()->getParam('shop_id');
        $categories = $this->categoryRepository->getByShopId($shopId);

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->getCategoryId(),
                'path' => $category->getPathWithCategoryId(),
                'is_valid' => $category->isCategoryValid()
            ];
        }

        $this->setJsonContent($result);

        return $this->getResult();
    }
}
