<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

class UpdateService
{
    private \M2E\TikTokShop\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository
    ) {
        $this->attributeService = $attributeService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    public function update(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary
    ): void {
        $shop = $dictionary->getShop();
        $categoryId = $dictionary->getCategoryId();

        try {
            $categoryData = $this->attributeService->getCategoryDataFromServer($shop, $categoryId);
            $authorizedBrandData = $this->attributeService->getBrandsDataFromServer($shop, $categoryId);

            $productAttributes = $this->attributeService->getProductAttributes($categoryData);
            $salesAttributes = $this->attributeService->getSalesAttributes($categoryData);
            $totalProductAttributes = $this->attributeService->getTotalProductAttributes($categoryData);
            $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);

            $dictionary->setAuthorizedBrands($authorizedBrandData->getBrands());
            $dictionary->setProductAttributes($productAttributes);
            $dictionary->setSalesAttributes($salesAttributes);
            $dictionary->setCategoryRules($categoryData->getRules());
            $dictionary->setTotalProductAttributes($totalProductAttributes);
            $dictionary->setHasRequiredProductAttributes($hasRequiredProductAttributes);
            $dictionary->markCategoryAsValid();
        } catch (\M2E\TikTokShop\Model\Exception\CategoryInvalid $exception) {
            $dictionary->markCategoryAsInvalid();
        }

        $this->categoryDictionaryRepository->save($dictionary);
    }
}
