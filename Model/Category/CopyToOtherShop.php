<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category;

class CopyToOtherShop
{
    private \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $categoryTreeLastSyncDateService;
    private \M2E\TikTokShop\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService;
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\TikTokShop\Model\Category\Dictionary\CreateService $categoryDictionaryCreateService;
    private \M2E\TikTokShop\Model\Category\CategoryAttributeFactory $categoryAttributeFactory;
    private \M2E\TikTokShop\Model\Category\Attribute\Manager $attributeManager;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Tree\LastSyncDateService $categoryTreeLastSyncDateService,
        \M2E\TikTokShop\Model\Category\Tree\SynchronizeService $categoryTreeSynchronizeService,
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\TikTokShop\Model\Category\Dictionary\CreateService $categoryDictionaryCreateService,
        \M2E\TikTokShop\Model\Category\CategoryAttributeFactory $categoryAttributeFactory,
        \M2E\TikTokShop\Model\Category\Attribute\Manager $attributeManager
    ) {
        $this->categoryTreeLastSyncDateService = $categoryTreeLastSyncDateService;
        $this->categoryTreeSynchronizeService = $categoryTreeSynchronizeService;
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->dictionaryRepository = $dictionaryRepository;
        $this->categoryDictionaryCreateService = $categoryDictionaryCreateService;
        $this->categoryAttributeFactory = $categoryAttributeFactory;
        $this->attributeManager = $attributeManager;
    }

    public function execute(Dictionary $sourceDictionary, \M2E\TikTokShop\Model\Shop $targetShop): Dictionary
    {
        $sourceCategoryId = $sourceDictionary->getCategoryId();
        $targetShopId = $targetShop->getId();

        if ($this->isExistCategoryDictionaryForShop($sourceCategoryId, $targetShopId)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                sprintf(
                    'Category Dictionary for category_id "%s" and shop_id "%s" existed.',
                    $sourceCategoryId,
                    $targetShopId
                )
            );
        }

        if ($this->categoryTreeLastSyncDateService->isNeedSynchronizeCategoryTree($targetShop)) {
            $this->categoryTreeSynchronizeService->synchronize($targetShop);
        }

        if (!$this->isExistCategoryTree($sourceCategoryId, $targetShopId)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                sprintf(
                    'Category Tree for category_id "%s" and shop_id "%s" not found.',
                    $sourceCategoryId,
                    $targetShopId
                )
            );
        }

        $newDictionary = $this->categoryDictionaryCreateService
            ->create($targetShop, $sourceCategoryId);
        $this->copyAttributes($sourceDictionary, $newDictionary);

        return $newDictionary;
    }

    private function isExistCategoryDictionaryForShop(string $categoryId, int $shopId): bool
    {
        $founded = $this->dictionaryRepository
            ->findByShopAndCategoryId($shopId, $categoryId);

        return $founded !== null;
    }

    private function isExistCategoryTree(string $categoryId, int $shopId): bool
    {
        $founded = $this->categoryTreeRepository
            ->findCategoryByShopIdAndCategoryId($shopId, $categoryId);

        return $founded !== null;
    }

    private function copyAttributes(Dictionary $sourceDictionary, Dictionary $targetDictionary): void
    {
        $targetAttributesById = [];

        foreach ($targetDictionary->getProductAttributes() as $attribute) {
            $targetAttributesById[$attribute->getId()] = $attribute;
        }

        foreach ($targetDictionary->getSalesAttributes() as $attribute) {
            $targetAttributesById[$attribute->getId()] = $attribute;
        }

        foreach ($targetDictionary->getBrandAndSizeChartAttributes() as $attribute) {
            $targetAttributesById[$attribute->getId()] = $attribute;
        }

        foreach ($targetDictionary->getCertificationsAttributes() as $attribute) {
            $targetAttributesById[$attribute->getId()] = $attribute;
        }

        $newAttributes = [];
        foreach ($sourceDictionary->getRelatedAttributes() as $sourceAttribute) {
            $targetAttribute = $targetAttributesById[$sourceAttribute->getAttributeId()] ?? null;
            if ($targetAttribute === null) {
                continue;
            }

            $newAttribute = $this->categoryAttributeFactory->create();
            $newAttributes[] = $newAttribute->create(
                $targetDictionary->getId(),
                $targetAttribute->getType(),
                $targetAttribute->getId(),
                $targetAttribute->getName(),
                $sourceAttribute->getValueMode(),
                $sourceAttribute->getRecommendedValue(),
                $sourceAttribute->getCustomValue(),
                $sourceAttribute->getCustomAttributeValue(),
            );
        }

        $this->attributeManager->createOrUpdateAttributes($newAttributes, $targetDictionary);
    }
}
