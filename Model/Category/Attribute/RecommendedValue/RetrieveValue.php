<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Attribute\RecommendedValue;

use M2E\TikTokShop\Model\Category\CategoryAttribute;
use M2E\TikTokShop\Model\Category\Dictionary\Attribute\ProductAttribute;
use M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value;

class RetrieveValue
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    /** @var array<int, \M2E\TikTokShop\Model\Category\Dictionary> */
    private array $dictionaries;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository
    ) {
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    public function tryRetrieve(
        CategoryAttribute $categoryAttribute,
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct
    ): ?Result {
        $result = null;

        $dictionary = $this->getDictionaryById($categoryAttribute->getCategoryDictionaryId());
        $productAttributes = $dictionary->getProductAttributes();

        $attribute = $this->getAttributeById($categoryAttribute->getAttributeId(), $productAttributes);

        if (empty($attribute->getValues()) || $attribute->isCustomised()) {
            return $result;
        }

        if ($categoryAttribute->isValueModeCustomValue()) {
            $attributeVal = $categoryAttribute->getCustomValue();

            $result = $this->processValue($categoryAttribute, $attribute, $attributeVal);
        } elseif ($categoryAttribute->isValueModeCustomAttribute()) {
            $attributeVal = $magentoProduct->getAttributeValue($categoryAttribute->getCustomAttributeValue());

            $result = $this->processValue($categoryAttribute, $attribute, $attributeVal);
        }

        return $result;
    }

    private function processValue(
        CategoryAttribute $categoryAttribute,
        ProductAttribute $attribute,
        string $attributeVal
    ): Result {
        $recommended = $this->findRecommendedIdByName($attribute->getValues(), $attributeVal);
        if (!empty($recommended)) {
            return Result::createSuccess($recommended);
        }

        if ($attribute->isRequired()) {
            return Result::createFail($this->compileErrorMessage($categoryAttribute));
        }

        return Result::createFail($this->compileWarningMessage($categoryAttribute));
    }

    /**
     * @param string $attributeId
     * @param ProductAttribute[] $productAttributes
     *
     * @return ProductAttribute
     */
    private function getAttributeById(string $attributeId, array $productAttributes): ProductAttribute
    {
        $attributes = [];
        foreach ($productAttributes as $productAttribute) {
            $attributes[$productAttribute->getId()] = $productAttribute;
        }

        return $attributes[$attributeId];
    }

    private function getDictionaryById(int $dictionaryId): \M2E\TikTokShop\Model\Category\Dictionary
    {
        if (empty($this->dictionaries[$dictionaryId])) {
            $this->dictionaries[$dictionaryId] = $this->categoryDictionaryRepository->get($dictionaryId);
        }

        return $this->dictionaries[$dictionaryId];
    }

    /**
     * @param Value[] $values
     * @param string $name
     *
     * @return string|null
     */
    private function findRecommendedIdByName(array $values, string $name): ?string
    {
        $result = null;

        $attributeName = $this->normalizeAttributeValue($name);
        foreach ($values as $attributeValue) {
            $attributeValueName = $this->normalizeAttributeValue($attributeValue->getName());

            if ($attributeName === $attributeValueName) {
                $result = $attributeValue->getId();
                break;
            }
        }

        return $result;
    }

    private function normalizeAttributeValue(string $value): string
    {
        $removePunctuation = str_replace([' ', '_', '-', '.'], '', $value);

        return strtolower($removePunctuation);
    }

    private function compileWarningMessage(CategoryAttribute $categoryAttribute): string
    {
        return (string)__(
            'The value set for the attribute: %attribute_title does not match any of the supported options and was not synchronized to the channel.',
            [
                'attribute_title' => $categoryAttribute->getAttributeName(),
            ]
        );
    }

    private function compileErrorMessage(CategoryAttribute $categoryAttribute): string
    {
        return (string)__(
            'Invalid value set for attribute: %attribute_title. The provided value does not match any of the supported options.',
            [
                'attribute_title' => $categoryAttribute->getAttributeName(),
            ]
        );
    }
}
