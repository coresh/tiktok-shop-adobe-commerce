<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class DictionaryMapper
{
    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @see \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Specific\Form\Element\Dictionary
     */
    public function getProductAttributes(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary
    ): array {
        $savedAttributes = $this->loadSavedAttributes($dictionary, [
            CategoryAttribute::ATTRIBUTE_TYPE_PRODUCT,
        ]);

        $attributes = [];
        foreach ($dictionary->getProductAttributes() as $productAttribute) {
            $item = $this->map($productAttribute, $savedAttributes);

            if ($item['required']) {
                array_unshift($attributes, $item);
                continue;
            }

            $attributes[] = $item;
        }

        return $this->sortAttributesByTitle($attributes);
    }

    public function getVirtualAttributes(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary
    ): array {
        $savedAttributes = $this->loadSavedAttributes($dictionary, [
            CategoryAttribute::ATTRIBUTE_TYPE_BRAND,
            CategoryAttribute::ATTRIBUTE_TYPE_SIZE_CHART,
        ]);

        $attributes = [];
        foreach ($dictionary->getBrandAndSizeChartAttributes() as $virtualAttribute) {
            $item = $this->map($virtualAttribute, $savedAttributes);

            if ($item['required']) {
                array_unshift($attributes, $item);
                continue;
            }

            $attributes[] = $item;
        }

        return $this->sortAttributesByTitle($attributes);
    }

    public function getCertificationsAttributes(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary
    ): array {
        $savedAttributes = $this->loadSavedAttributes($dictionary, [
            CategoryAttribute::ATTRIBUTE_TYPE_CERTIFICATE,
        ]);

        $certificationAttributes = [];
        foreach ($dictionary->getCertificationsAttributes() as $certificationAttribute) {
            $item = $this->map($certificationAttribute, $savedAttributes);
            $attributeVariants = $this->getCertificateAttributeVariants($certificationAttribute, $savedAttributes);

            if ($item['required']) {
                array_unshift($certificationAttributes, $item, ...$attributeVariants);
                continue;
            }

            array_unshift($attributeVariants, $item);
            $certificationAttributes = array_merge($certificationAttributes, $attributeVariants);
        }

        return $this->sortAttributesByTitle($certificationAttributes);
    }

    private function map(
        \M2E\TikTokShop\Model\Category\Dictionary\AbstractAttribute $attribute,
        array $savedAttributes
    ): array {
        $item = [
            'id' => $attribute->getId(),
            'title' => $attribute->getName(),
            'attribute_type' => $attribute->getType(),
            'type' => $attribute->isMultipleSelected() ? 'select_multiple' : 'select',
            'required' => $attribute->isRequired(),
            'min_values' => $attribute->isRequired() ? 1 : 0,
            'max_values' => $attribute->isMultipleSelected() ? count($attribute->getValues()) : 1,
            'is_customized' => $attribute->isCustomised(),
            'values' => [],
            'template_attribute' => [],
            'sample_image_url' => $attribute->getSampleImageUrl()
        ];

        $existsAttribute = $savedAttributes[$attribute->getId()] ?? null;
        if ($existsAttribute) {
            $item['template_attribute'] = [
                'id' => $existsAttribute->getAttributeId(),
                'template_category_id' => $existsAttribute->getId(),
                'mode' => '1',
                'attribute_title' => $existsAttribute->getAttributeId(),
                'value_mode' => $existsAttribute->getValueMode(),
                'value_tiktokshop_recommended' => $existsAttribute->getRecommendedValue(),
                'value_custom_value' => $existsAttribute->getCustomValue(),
                'value_custom_attribute' => $existsAttribute->getCustomAttributeValue(),
            ];
        }

        foreach ($attribute->getValues() as $value) {
            $item['values'][] = [
                'id' => $value->getId(),
                'value' => $value->getName(),
            ];
        }

        return $item;
    }

    private function loadSavedAttributes(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary,
        array $typeFilter = []
    ): array {
        $attributes = [];

        $savedAttributes = $this
            ->attributeRepository
            ->findByDictionaryId($dictionary->getId(), $typeFilter);

        foreach ($savedAttributes as $attribute) {
            $attributes[$attribute->getAttributeId()] = $attribute;
        }

        return $attributes;
    }

    public function sortAttributesByTitle(array $attributes): array
    {
        usort($attributes, function ($prev, $next) {
            return strcmp($prev['title'], $next['title']);
        });

        $requiredAttributes = [];
        foreach ($attributes as $index => $attribute) {
            if (isset($attribute['required']) && $attribute['required'] === true) {
                $requiredAttributes[] = $attribute;
                unset($attributes[$index]);
            }
        }

        return array_merge($requiredAttributes, $attributes);
    }

    private function getCertificateAttributeVariants(
        \M2E\TikTokShop\Model\Category\Dictionary\Attribute\CertificateAttribute $attribute,
        array $savedAttributes
    ): array {
        $variants = [];

        foreach ($savedAttributes as $savedAttribute) {
            if ($this->isMatchingAttribute($attribute, $savedAttribute)) {
                $modifiedAttribute = clone $attribute;
                $modifiedAttribute->setId($savedAttribute->getAttributeId());
                $variants[] = $this->map(
                    $modifiedAttribute,
                    [$savedAttribute->getAttributeId() => $savedAttribute]
                );
            }
        }

        return $variants;
    }

    private function isMatchingAttribute(
        \M2E\TikTokShop\Model\Category\Dictionary\Attribute\CertificateAttribute $attribute,
        \M2E\TikTokShop\Model\Category\CategoryAttribute $savedAttribute
    ): bool {
        $savedId = $savedAttribute->getAttributeId();
        $cleanedId = \M2E\TikTokShop\Model\Category\CategoryAttribute::getCleanAttributeId($savedId);

        return $savedId !== $attribute->getId()
            && $cleanedId === $attribute->getId();
    }
}
