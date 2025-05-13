<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class DictionaryMapper
{
    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\TikTokShop\Model\AttributeMapping\GeneralService $generalService;

    /** @var \M2E\Core\Model\AttributeMapping\Pair[] */
    private array $generalAttributeMapping;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\TikTokShop\Model\AttributeMapping\GeneralService $generalService
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->generalService = $generalService;
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

        $generalMappingAttributes = $this->getGeneralAttributesMappingByAttributeId();

        $attributes = [];
        foreach ($dictionary->getProductAttributes() as $productAttribute) {
            $item = $this->map($productAttribute, $savedAttributes, $generalMappingAttributes);

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

        $generalMappingAttributes = $this->getGeneralAttributesMappingByAttributeId();

        $attributes = [];
        foreach ($dictionary->getBrandAndSizeChartAttributes() as $virtualAttribute) {
            $item = $this->map($virtualAttribute, $savedAttributes, $generalMappingAttributes);

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

        $generalMappingAttributes = $this->getGeneralAttributesMappingByAttributeId();

        $certificationAttributes = [];
        foreach ($dictionary->getCertificationsAttributes() as $certificationAttribute) {
            $item = $this->map($certificationAttribute, $savedAttributes, $generalMappingAttributes);
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

    /**
     * @param \M2E\TikTokShop\Model\Category\Dictionary\AbstractAttribute $attribute
     * @param \M2E\TikTokShop\Model\Category\CategoryAttribute[] $savedAttributes
     * @param \M2E\Core\Model\AttributeMapping\Pair[] $generalMappingAttributes
     *
     * @return array
     */
    private function map(
        \M2E\TikTokShop\Model\Category\Dictionary\AbstractAttribute $attribute,
        array $savedAttributes,
        array $generalMappingAttributes = []
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
        $generalMapping = $generalMappingAttributes[$attribute->getId()] ?? null;
        if (
            $existsAttribute !== null
            || $generalMapping !== null
        ) {
            $item['template_attribute'] = [
                'id' => $existsAttribute ? $existsAttribute->getAttributeId() : null,
                'template_category_id' => $existsAttribute ? $existsAttribute->getId() : null,
                'mode' => '1',
                'attribute_title' => $existsAttribute ? $existsAttribute->getAttributeId() : $attribute->getName(),
                'value_mode' => $existsAttribute !== null
                    ? $existsAttribute->getValueMode()
                    : ($generalMapping !== null ? \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE : \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_NONE),
                'value_tiktokshop_recommended' => $existsAttribute ? $existsAttribute->getRecommendedValue() : null,
                'value_custom_value' => $existsAttribute ? $existsAttribute->getCustomValue() : null,
                'value_custom_attribute' => $existsAttribute !== null
                    ? $existsAttribute->getCustomAttributeValue()
                    : ($generalMapping !== null ? $generalMapping->getMagentoAttributeCode() : null),            ];
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
        $generalMappingAttributes = $this->getGeneralAttributesMappingByAttributeId();

        $variants = [];
        foreach ($savedAttributes as $savedAttribute) {
            if ($this->isMatchingAttribute($attribute, $savedAttribute)) {
                $modifiedAttribute = clone $attribute;
                $modifiedAttribute->setId($savedAttribute->getAttributeId());
                $variants[] = $this->map(
                    $modifiedAttribute,
                    [$savedAttribute->getAttributeId() => $savedAttribute],
                    $generalMappingAttributes
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

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    private function getGeneralAttributesMappingByAttributeId(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->generalAttributeMapping)) {
            return $this->generalAttributeMapping;
        }

        $result = [];
        foreach ($this->generalService->getAll() as $item) {
            $result[$item->getChannelAttributeCode()] = $item;
        }

        return $this->generalAttributeMapping = $result;
    }
}
