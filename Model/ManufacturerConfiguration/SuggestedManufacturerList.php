<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

class SuggestedManufacturerList
{
    private const MANUFACTURER_ATTRIBUTE_CODE = 'manufacturer';

    private \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository;

    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    public function get(): array
    {
        try {
            $attributeOptions = $this
                ->attributeRepository
                ->get(self::MANUFACTURER_ATTRIBUTE_CODE)
                ->getOptions();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $attributeOptions = [];
        }

        $result = [];
        foreach ($attributeOptions as $attributeOption) {
            $result[] = [
                'id' => $attributeOption->getSortOrder(),
                'label' => $attributeOption->getLabel(),
            ];
        }

        return $result;
    }
}
