<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

use M2E\TikTokShop\Helper\Component\TikTokShop\Configuration;

abstract class AbstractDimensionFinder
{
    /** @var \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration */
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    abstract public function find(\M2E\TikTokShop\Model\Product $product): object;

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotFoundAttributeValueException
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotConfiguredException
     */
    protected function getPackageDimensionValue(
        string $type,
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct
    ): float {
        $mode = $this->configuration->getPackageDimensionMode($type);
        if ($mode === Configuration::PACKAGE_MODE_CUSTOM_VALUE) {
            return $this->getValueFromCustomValue($type);
        }

        if ($mode === Configuration::PACKAGE_MODE_CUSTOM_ATTRIBUTE) {
            $attributeCode = $this->configuration->getPackageDimensionCustomAttribute($type);
            if (empty($attributeCode)) {
                throw new NotConfiguredException($type);
            }

            return $this->getValueFromMagentoAttribute($type, $attributeCode, $magentoProduct);
        }

        throw new NotConfiguredException($type);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotConfiguredException
     */
    private function getValueFromCustomValue(string $type): float
    {
        $customValue = $this->configuration->getPackageDimensionCustomValue($type);

        if (empty($customValue)) {
            throw new NotConfiguredException($type);
        }

        return (float)$customValue;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotFoundAttributeValueException
     */
    private function getValueFromMagentoAttribute(
        $type,
        string $attributeCode,
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct
    ): float {
        $attributeValue = (float)$magentoProduct->getAttributeValue($attributeCode);

        if (empty($attributeValue)) {
            throw new NotFoundAttributeValueException($type, $attributeCode);
        }

        return $attributeValue;
    }

    protected function toInteger(float $length): int
    {
        return (int)ceil($length);
    }

    protected function toFloat(float $length): float
    {
        return round($length, 2);
    }
}
