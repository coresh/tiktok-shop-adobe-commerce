<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

class NotConfiguredException extends PackageDimensionException
{
    public function __construct(string $type)
    {
        parent::__construct($this->getMessageByType($type));
    }

    private function getMessageByType(string $type): string
    {
        switch ($type) {
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_WEIGHT:
                return (string)__('Package Weight not configured');
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_LENGTH:
                return (string)__('Package Length not configured');
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_WIDTH:
                return (string)__('Package Width not configured');
            case \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_HEIGHT:
                return (string)__('Package Height not configured');
            default:
                return (string)__('N/A');
        }
    }
}
