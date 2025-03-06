<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

class AttributeService
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Processor $attributeGetProcessor;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get\Processor $brandGetProcessor;
    private \M2E\TikTokShop\Model\Category\Dictionary\AttributeFactory $attributeFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\Get\Processor $recommendedCategoryGetProcessor;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Processor $attributeGetProcessor,
        \M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\Get\Processor $recommendedCategoryGetProcessor,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get\Processor $brandGetProcessor,
        \M2E\TikTokShop\Model\Category\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeGetProcessor = $attributeGetProcessor;
        $this->recommendedCategoryGetProcessor = $recommendedCategoryGetProcessor;
        $this->brandGetProcessor = $brandGetProcessor;
        $this->attributeFactory = $attributeFactory;
    }

    public function getCategoryDataFromServer(
        \M2E\TikTokShop\Model\Shop $shop,
        string $categoryId
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Response {
        return $this->attributeGetProcessor
            ->process($shop->getAccount(), $shop, $categoryId);
    }

    public function getRecommendedCategoryDataFromServer(
        \M2E\TikTokShop\Model\Shop $shop,
        string $productTitle
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\Get\Response {
        return $this->recommendedCategoryGetProcessor
            ->process($shop->getAccount(), $shop, $productTitle);
    }

    public function getBrandsDataFromServer(
        \M2E\TikTokShop\Model\Shop $shop,
        string $categoryId
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get\Response {
        return $this->brandGetProcessor
            ->processAuthorizedBrands($shop->getAccount(), $shop, $categoryId);
    }

    public function getProductAttributes(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Response $categoryData
    ): array {
        $productAttributes = [];
        foreach ($categoryData->getAttributes() as $responseAttribute) {
            if ($responseAttribute->isSalesType()) {
                continue;
            }

            $values = [];
            foreach ($responseAttribute->getValues() as $value) {
                $values[] = $this->attributeFactory->createValue(
                    $value['id'],
                    $value['name']
                );
            }

            $productAttributes[] = $this->attributeFactory->createProductAttribute(
                $responseAttribute->getId(),
                $responseAttribute->getName(),
                $responseAttribute->isRequired(),
                $responseAttribute->isCustomised(),
                $responseAttribute->isMultipleSelected(),
                $values
            );
        }

        return $productAttributes;
    }

    public function getSalesAttributes(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Response $categoryData
    ): array {
        $salesAttributes = [];
        foreach ($categoryData->getAttributes() as $responseAttribute) {
            if ($responseAttribute->isProductType()) {
                continue;
            }
            $salesAttributes[] = $this->attributeFactory->createSalesAttribute(
                $responseAttribute->getId(),
                $responseAttribute->getName(),
                $responseAttribute->isRequired(),
                $responseAttribute->isCustomised(),
                $responseAttribute->isMultipleSelected()
            );
        }

        return $salesAttributes;
    }

    public function getTotalProductAttributes(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Response $categoryData
    ): int {
        $productAttributesCount = 0;

        foreach ($categoryData->getAttributes() as $attribute) {
            if ($attribute->isProductType()) {
                $productAttributesCount++;
            }
        }

        $productAttributesCount++; // +1 for brand attribute

        $categoryRules = $categoryData->getRules();

        // + size chart attribute
        if ($categoryRules['size_chart']['is_supported'] ?? false) {
            ++$productAttributesCount;
        }

        // + product certifications attributes
        if ($categoryRules['product_certifications'] ?? false) {
            $productAttributesCount += count($categoryRules['product_certifications']);
        }

        return $productAttributesCount;
    }

    public function getHasRequiredAttributes(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Response $categoryData
    ): bool {
        foreach ($categoryData->getAttributes() as $attribute) {
            if ($attribute->isProductType() && $attribute->isRequired()) {
                return true;
            }
        }

        return false;
    }
}
