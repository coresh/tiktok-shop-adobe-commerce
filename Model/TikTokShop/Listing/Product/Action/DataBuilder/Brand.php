<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class Brand extends AbstractDataBuilder
{
    public const NICK = 'Brand';

    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;

    private ?string $brandName = null;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array{brand_id: string, brand_name: string}
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getBuilderData(): array
    {
        $response = [
            'brand_id' => $this->getListingProduct()->getOnlineBrandId(),
            'brand_name' => '',
        ];

        $categoryId = $this->getListingProduct()->getTemplateCategoryId();

        $brandAttributes = $this->attributeRepository->findByDictionaryId($categoryId, [
            CategoryAttribute::ATTRIBUTE_TYPE_BRAND,
        ]);

        if (count($brandAttributes) === 0) {
            return $response;
        }

        $brandAttribute = reset($brandAttributes);

        if ($brandAttribute->isObjectNew()) {
            return $response;
        }

        if ($brandAttribute->isValueModeNone()) {
            return $response;
        }

        if ($brandAttribute->isValueModeRecommended()) {
            if (!$brandAttribute->getRecommendedValue()) {
                return $response;
            }

            $value = $brandAttribute->getRecommendedValue();
            $response['brand_id'] = reset($value);

            $this->brandName = $response['brand_name'];

            return $response;
        }

        if ($brandAttribute->isValueModeCustomAttribute()) {
            $attributeCode = $brandAttribute->getCustomAttributeValue();
            $magentoProduct = $this->getListingProduct()->getMagentoProduct();

            $this->searchNotFoundAttributes($magentoProduct);
            $attributeValue = $magentoProduct->getAttributeValue($attributeCode);
            $this->processNotFoundAttributes((string)__('Brand'), $magentoProduct);

            if (empty($attributeValue)) {
                return $response;
            }

            $currentBrandName = $this->getListingProduct()->getOnlineBrandName();
            if (!empty($currentBrandName) && $attributeValue !== $currentBrandName) {
                $response['brand_id'] = null;
            }

            $response['brand_name'] = $attributeValue;

            $this->brandName = $response['brand_name'];

            return $response;
        }

        if ($brandAttribute->isValueModeCustomValue()) {
            $attributeVal = $brandAttribute->getCustomValue();

            if (!$attributeVal) {
                return $response;
            }

            $response['brand_name'] = $attributeVal;
            $this->brandName = $response['brand_name'];

            $currentBrandName = $this->getListingProduct()->getOnlineBrandName();
            if (!empty($currentBrandName) && $attributeVal !== $currentBrandName) {
                $response['brand_id'] = null;
            }

            return $response;
        }

        return $response;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'online_brand_name' => $this->brandName,
            ],
        ];
    }
}
