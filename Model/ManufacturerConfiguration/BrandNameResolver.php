<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class BrandNameResolver
{
    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    public function resolve(\M2E\TikTokShop\Model\Product $product): BrandNameResolverResult
    {
        $brandCategoryAttribute = $this->findProductBrandCategoryAttribute($product);
        if (empty($brandCategoryAttribute)) {
            return BrandNameResolverResult::newFail('Not found Brand Category Attribute');
        }

        switch ($brandCategoryAttribute->getValueMode()) {
            case \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_NONE:
                return BrandNameResolverResult::newFail('Brand Category Attribute has mode NONE');
            case \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_RECOMMENDED:
                return $this->resolveByRecommendedMode($brandCategoryAttribute, $product);
            case \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_VALUE:
                return $this->resolveByCustomValueMode($brandCategoryAttribute);
            case \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE:
                return $this->resolveByCustomAttributeMode($brandCategoryAttribute, $product);
        }

        return BrandNameResolverResult::newFail('Incorrect Brand Category Attribute value mode');
    }

    private function findProductBrandCategoryAttribute(
        \M2E\TikTokShop\Model\Product $product
    ): ?CategoryAttribute {
        $brandAttributes = $this->attributeRepository
            ->findByDictionaryId(
                $product->getTemplateCategoryId(),
                [CategoryAttribute::ATTRIBUTE_TYPE_BRAND]
            );

        if (count($brandAttributes) === 0) {
            return null;
        }

        /** @var CategoryAttribute $brandAttribute */
        $brandAttribute = reset($brandAttributes);

        if ($brandAttribute->isObjectNew()) {
            return null;
        }

        return $brandAttribute;
    }

    private function resolveByRecommendedMode(
        CategoryAttribute $brandCategoryAttribute,
        \M2E\TikTokShop\Model\Product $product
    ): BrandNameResolverResult {
        $recommendedValues = $brandCategoryAttribute->getRecommendedValue();
        if (empty($recommendedValues)) {
            return BrandNameResolverResult::newFail('Brand Category Attribute recommended value is empty');
        }

        $brandId = reset($recommendedValues);

        $brandName = null;
        foreach ($product->getCategoryDictionary()->getAuthorizedBrands() as $authorizedBrand) {
            if ($authorizedBrand['id'] !== $brandId) {
                continue;
            }

            $brandName = $authorizedBrand['name'];
        }

        if ($brandName === null) {
            return BrandNameResolverResult::newFail('Not Found Brand in Authorized Brands');
        }

        return BrandNameResolverResult::newSuccess($brandName);
    }

    private function resolveByCustomValueMode(
        CategoryAttribute $brandCategoryAttribute
    ): BrandNameResolverResult {
        $attributeValue = $brandCategoryAttribute->getCustomValue();
        if (!$attributeValue) {
            return BrandNameResolverResult::newFail('Brand Category Attribute custom value is empty');
        }

        return BrandNameResolverResult::newSuccess($attributeValue);
    }

    private function resolveByCustomAttributeMode(
        CategoryAttribute $brandCategoryAttribute,
        \M2E\TikTokShop\Model\Product $product
    ): BrandNameResolverResult {
        $magentoProduct = $product->getMagentoProduct();
        $attributeCode = $brandCategoryAttribute->getCustomAttributeValue();

        $magentoProduct->clearNotFoundAttributes();
        $magentoAttributeValue = $magentoProduct->getAttributeValue($attributeCode);
        $notFoundAttributes = $magentoProduct->getNotFoundAttributes();

        if (empty($magentoAttributeValue)) {
            if (count($notFoundAttributes) > 0) {
                return BrandNameResolverResult::newFail('Brand Category Attribute attribute not found');
            }

            return BrandNameResolverResult::newFail('Brand Category Attribute attribute value is empty');
        }

        return BrandNameResolverResult::newSuccess($magentoAttributeValue);
    }
}
