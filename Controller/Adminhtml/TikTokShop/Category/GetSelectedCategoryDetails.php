<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class GetSelectedCategoryDetails extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\Tree\Repository $treeRepository;
    private \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Tree\Repository $treeRepository,
        \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder
    ) {
        parent::__construct();

        $this->treeRepository = $treeRepository;
        $this->pathBuilder = $pathBuilder;
    }

    public function execute()
    {
        $shopId = $this->getRequest()->getParam('shop_id');
        $categoryId = $this->getRequest()->getParam('value');

        if (
            empty($shopId)
            || empty($categoryId)
        ) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Invalid input');
        }

        $category = $this->treeRepository->getCategoryByShopIdAndCategoryId((int)$shopId, $categoryId);
        if ($category === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Category invalid');
        }

        $path = $this->pathBuilder->getPath($category);
        $details = [
            'path' => $path,
            'interface_path' => sprintf('%s (%s)', $path, $categoryId),
            'template_id' => null,
            'is_custom_template' => null,
        ];

        $this->setJsonContent($details);

        return $this->getResult();
    }
}
