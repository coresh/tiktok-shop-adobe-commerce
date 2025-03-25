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

    public function get(): ?array
    {
        $attributeOptions = $this
            ->attributeRepository
            ->get(self::MANUFACTURER_ATTRIBUTE_CODE)
            ->getOptions();

        $result = [];
        foreach ($attributeOptions as $attributeOption) {
            $result[] = ['id' => $attributeOption->getSortOrder(), 'label' => $attributeOption->getLabel()];
        }

        return $result;
    }
}
