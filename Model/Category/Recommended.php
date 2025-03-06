<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category;

use M2E\TikTokShop\Model\Category\Search\ResultItem;

class Recommended
{
    private \M2E\TikTokShop\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryRepository;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryRepository,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository
    ) {
        $this->attributeService = $attributeService;
        $this->categoryRepository = $categoryRepository;
        $this->shopRepository = $shopRepository;
        $this->pathBuilder = $pathBuilder;
        $this->dictionaryRepository = $dictionaryRepository;
    }

    public function process(int $shopId, string $searchQuery): ?\M2E\TikTokShop\Model\Category\Search\ResultItem
    {
        $shop = $this->shopRepository->get($shopId);

        $recommendedCategoryData = $this->attributeService->getRecommendedCategoryDataFromServer($shop, $searchQuery);
        $recommendedCategoryId = $recommendedCategoryData->geCategoryId();
        if (!isset($recommendedCategoryId)) {
            return null;
        }

        $category = $this->categoryRepository->getCategoryByShopIdAndCategoryId($shopId, $recommendedCategoryId);
        if (!isset($category)) {
            return null;
        }

        return new ResultItem(
            $category->getCategoryId(),
            $this->pathBuilder->getPath($category),
            $category->isInviteOnly(),
            $this->isValidCategory($category)
        );
    }

    private function isValidCategory(Tree $treeItem): bool
    {
        $dictionary = $this->dictionaryRepository
            ->findByShopAndCategoryId($treeItem->getShopId(), $treeItem->getCategoryId());

        if (!isset($dictionary)) {
            return true;
        }

        return $dictionary->isCategoryValid();
    }
}
