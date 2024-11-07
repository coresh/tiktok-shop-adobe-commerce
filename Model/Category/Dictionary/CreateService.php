<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

class CreateService
{
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\TikTokShop\Model\Category\DictionaryFactory $dictionaryFactory;
    private \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder;
    private \M2E\TikTokShop\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\DictionaryFactory $dictionaryFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder
    ) {
        $this->dictionaryFactory = $dictionaryFactory;
        $this->attributeService = $attributeService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->pathBuilder = $pathBuilder;
        $this->categoryTreeRepository = $categoryTreeRepository;
    }

    public function create(
        \M2E\TikTokShop\Model\Shop $shop,
        string $categoryId
    ): \M2E\TikTokShop\Model\Category\Dictionary {
        $treeNode = $this->categoryTreeRepository
            ->getCategoryByShopIdAndCategoryId($shop->getId(), $categoryId);

        if ($treeNode === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Not found category tree');
        }

        $categoryData = $this->attributeService->getCategoryDataFromServer($shop, $categoryId);
        $authorizedBrandData = $this->attributeService->getBrandsDataFromServer($shop, $categoryId);

        $productAttributes = $this->attributeService->getProductAttributes($categoryData);
        $salesAttributes = $this->attributeService->getSalesAttributes($categoryData);
        $totalProductAttributes = $this->attributeService->getTotalProductAttributes($categoryData);
        $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);

        $dictionary = $this->dictionaryFactory->create()->create(
            $shop->getId(),
            $categoryId,
            $this->pathBuilder->getPath($treeNode),
            $salesAttributes,
            $productAttributes,
            $categoryData->getRules(),
            $authorizedBrandData->getBrands(),
            $totalProductAttributes,
            $hasRequiredProductAttributes
        );

        $this->categoryDictionaryRepository->create($dictionary);

        return $dictionary;
    }
}
