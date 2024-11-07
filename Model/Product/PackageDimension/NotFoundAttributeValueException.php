<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

class NotFoundAttributeValueException extends PackageDimensionException
{
    public function __construct(string $type, string $attributeCode)
    {
        $message = (string)__(
            '%1: Attribute %2 were not found in this Product and its value was not sent.',
            $this->getAttributeTitleByType($type),
            $attributeCode,
        );

        parent::__construct($message);
    }

    private function getAttributeTitleByType(string $type): string
    {
        switch ($type) {
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_WEIGHT:
                return (string)__('Package weight');
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_LENGTH:
                return (string)__('Package length');
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_WIDTH:
                return (string)__('Package width');
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_HEIGHT:
                return (string)__('Package height');
            default:
                return (string)__('N/A');
        }
    }
}
