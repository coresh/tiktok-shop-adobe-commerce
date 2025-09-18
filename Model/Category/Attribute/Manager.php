<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Attribute;

class Manager
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\TikTokShop\Model\Category\Attribute\Repository $categoryAttributeRepository;
    private \Magento\Framework\App\ResourceConnection $resource;
    private \M2E\TikTokShop\Model\TikTokShop\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Template\Category\DiffFactory $diffFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Template\Category\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\TikTokShop\Model\AttributeMapping\GeneralService $attributeMappingGeneralService;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Listing\Wizard\Repository $listingWizardRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\TikTokShop\Model\Category\Attribute\Repository $categoryAttributeRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\TikTokShop\Model\TikTokShop\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory,
        \M2E\TikTokShop\Model\TikTokShop\Template\Category\DiffFactory $diffFactory,
        \M2E\TikTokShop\Model\TikTokShop\Template\Category\ChangeProcessorFactory $changeProcessorFactory,
        \M2E\TikTokShop\Model\TikTokShop\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\TikTokShop\Model\AttributeMapping\GeneralService $attributeMappingGeneralService,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Listing\Wizard\Repository $listingWizardRepository
    ) {
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->resource = $resource;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->attributeMappingGeneralService = $attributeMappingGeneralService;
        $this->productRepository = $productRepository;
        $this->listingWizardRepository = $listingWizardRepository;
    }

    /**
     * @param \M2E\TikTokShop\Model\Category\CategoryAttribute[] $attributes
     * @param \M2E\TikTokShop\Model\Category\Dictionary $dictionary
     *
     * @return void
     * @throws \Exception|\Throwable
     */
    public function createOrUpdateAttributes(
        array $attributes,
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary
    ): void {
        $attributesSortedById = [];
        $countOfUsedAttributes = 0;

        foreach ($attributes as $attribute) {
            $attributesSortedById[$attribute->getAttributeId()] = $attribute;
            if (!$this->isEmptyValues($attribute)) {
                $countOfUsedAttributes++;
            }
        }

        $transaction = $this->resource->getConnection()->beginTransaction();
        try {
            $oldSnapshot = $this->getSnapshot($dictionary);

            $existedAttributes = $this->categoryAttributeRepository
                ->findByDictionaryId($dictionary->getId());

            foreach ($existedAttributes as $existedAttribute) {
                $inputAttribute = $attributesSortedById[$existedAttribute->getAttributeId()] ?? null;
                if ($inputAttribute === null) {
                    continue;
                }

                $existedAttributeId = $inputAttribute->getAttributeId();
                if ($this->isEmptyAdditionalAttribute($inputAttribute)) {
                    $this->categoryAttributeRepository->delete($existedAttribute);
                    unset($attributesSortedById[$existedAttributeId]);
                } else {
                    $this->updateAttribute($existedAttribute, $inputAttribute);
                    unset($attributesSortedById[$existedAttribute->getAttributeId()]);
                }
            }

            foreach ($attributesSortedById as $attribute) {
                if ($this->isEmptyAdditionalAttribute($attribute)) {
                    continue;
                }
                $this->createAttribute($attribute);
            }

            $newSnapshot = $this->getSnapshot($dictionary);

            $this->addInstruction($dictionary, $oldSnapshot, $newSnapshot);

            $dictionary->setUsedProductAttributes($countOfUsedAttributes);
            $dictionary->installStateSaved();
            $this->categoryDictionaryRepository->save($dictionary);

            $this->attributeMappingGeneralService->create($dictionary->getRelatedAttributes());
            $this->resetCategoryAttributesValidation($dictionary->getId());
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        $transaction->commit();
    }

    private function updateAttribute(
        \M2E\TikTokShop\Model\Category\CategoryAttribute $existedAttribute,
        \M2E\TikTokShop\Model\Category\CategoryAttribute $inputAttribute
    ) {
        $existedAttribute->setCategoryDictionaryId($inputAttribute->getCategoryDictionaryId());
        $existedAttribute->setAttributeType($inputAttribute->getAttributeType());
        $existedAttribute->setAttributeId($inputAttribute->getAttributeId());
        $existedAttribute->setAttributeName($inputAttribute->getAttributeName());
        $existedAttribute->setValueMode($inputAttribute->getValueMode());
        $existedAttribute->setRecommendedValue($inputAttribute->getRecommendedValue());
        $existedAttribute->setCustomValue($inputAttribute->getCustomValue());
        $existedAttribute->setCustomAttributeValue($inputAttribute->getCustomAttributeValue());

        $this->categoryAttributeRepository->save($existedAttribute);
    }

    private function createAttribute(\M2E\TikTokShop\Model\Category\CategoryAttribute $attribute)
    {
        $this->categoryAttributeRepository->create($attribute);
    }

    private function getSnapshot(\M2E\TikTokShop\Model\Category\Dictionary $dictionary): array
    {
        return $this->snapshotBuilderFactory
            ->create()
            ->setModel($dictionary)
            ->getSnapshot();
    }

    private function addInstruction(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary,
        array $oldSnapshot,
        array $newSnapshot
    ): void {
        $diff = $this->diffFactory->create();
        $diff->setOldSnapshot($oldSnapshot);
        $diff->setNewSnapshot($newSnapshot);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($dictionary);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }

    private function isEmptyValues(\M2E\TikTokShop\Model\Category\CategoryAttribute $attribute): bool
    {
        return empty($attribute->getCustomValue())
            && empty($attribute->getCustomAttributeValue())
            && empty($attribute->getRecommendedValue());
    }

    private function isEmptyAdditionalAttribute(\M2E\TikTokShop\Model\Category\CategoryAttribute $attribute): bool
    {
        return \M2E\TikTokShop\Model\Category\CategoryAttribute::isAdditionalAttributeId($attribute->getAttributeId())
            && $this->isEmptyValues($attribute);
    }

    private function resetCategoryAttributesValidation(int $categoryId): void
    {
        $this->productRepository->resetCategoryAttributesValidationData($categoryId);
        $this->listingWizardRepository->resetCategoryAttributesValidationDataByCategoryId($categoryId);
    }
}
