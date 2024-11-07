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
    ): \M2E\TikTokShop\Model\Category\Dictionary {
        $shop = $dictionary->getShop();
        $categoryId = $dictionary->getCategoryId();

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

        $this->categoryDictionaryRepository->save($dictionary);

        return $dictionary;
    }
}
