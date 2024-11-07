<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class Categories extends AbstractDataBuilder
{
    public const NICK = 'Categories';

    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;

    private string $onlineCategoryId = '';
    private string $onlineCategoriesData = '';

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array{category_id: string, product_attributes: array}
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getBuilderData(): array
    {
        $category = $this->getListingProduct()->getCategoryDictionary();

        $data = [
            'category_id' => $category->getCategoryId(),
            'product_attributes' => [],
        ];

        $productAttributeData = $this->getProductAttributeData(
            $this->getListingProduct(),
        );

        foreach ($productAttributeData as $attributeId => $values) {
            if (!empty($values)) {
                $data['product_attributes'][] = [
                    'id' => (string)$attributeId,
                    'values' => $values,
                ];
            }
        }

        $this->onlineCategoryId = $data['category_id'];
        $this->onlineCategoriesData = json_encode($data['product_attributes']);

        return $data;
    }

    private function getProductAttributeData(\M2E\TikTokShop\Model\Product $listingProduct): array
    {
        $categoryId = $listingProduct->getTemplateCategoryId();

        $attributes = $this->attributeRepository->findByDictionaryId($categoryId, [
            CategoryAttribute::ATTRIBUTE_TYPE_PRODUCT,
            CategoryAttribute::ATTRIBUTE_TYPE_SALES,
        ]);

        $result = [];

        foreach ($attributes as $attribute) {
            if ($attribute->isValueModeNone()) {
                $result[$attribute->getAttributeId()] = [];
                continue;
            }

            if ($attribute->isValueModeRecommended()) {
                foreach ($attribute->getRecommendedValue() as $valueId) {
                    $result[$attribute->getAttributeId()][] = ['id' => $valueId];
                }

                continue;
            }

            if ($attribute->isValueModeCustomValue()) {
                $attributeVal = $attribute->getCustomValue();
                if (!empty($attributeVal)) {
                    $result[$attribute->getAttributeId()][] = ['name' => $attributeVal];
                }
            }

            if ($attribute->isValueModeCustomAttribute()) {
                $magentoProduct = $listingProduct->getMagentoProduct();
                $attributeVal = $magentoProduct->getAttributeValue($attribute->getCustomAttributeValue());
                if (!empty($attributeVal)) {
                    $result[$attribute->getAttributeId()][] = ['name' => $attributeVal];
                }
            }
        }

        return $result;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'online_category_id' => $this->onlineCategoryId,
                'online_category_data' => $this->onlineCategoriesData,
            ],
        ];
    }
}
