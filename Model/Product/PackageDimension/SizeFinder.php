<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

class SizeFinder extends AbstractDimensionFinder
{
    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException
     */
    public function find(\M2E\TikTokShop\Model\Product $product): Size
    {
        $unit = $this->getUnit($product);

        try {
            return new Size(
                $this->getPackageLength($product->getMagentoProduct()),
                $this->getPackageWidth($product->getMagentoProduct()),
                $this->getPackageHeight($product->getMagentoProduct()),
                $unit
            );
        } catch (NotConfiguredException $exception) {
            throw $exception;
        } catch (PackageDimensionException $exception) {
        }

        $childSizes = [];
        foreach ($product->getVariants() as $variant) {
            try {
                $packageSize = new Size(
                    $this->getPackageLength($variant->getMagentoProduct()),
                    $this->getPackageWidth($variant->getMagentoProduct()),
                    $this->getPackageHeight($variant->getMagentoProduct()),
                    $unit
                );
                $childSizes[$packageSize->getVolumeWeight()] = $packageSize;
            } catch (PackageDimensionException $exception) {
                continue;
            }
        }

        if (count($childSizes) === 0) {
            throw new PackageDimensionException(
                (string)__('Package Dimensions are missing. To list the Product, please make sure that the Package settings are correct.'),
                [],
                \M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException::CODE_DIMENSIONS_MISSING
            );
        }

        krsort($childSizes, SORT_NUMERIC);

        return reset($childSizes);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotConfiguredException
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotFoundAttributeValueException
     */
    private function getPackageLength(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): string
    {
        return (string)$this->toInteger(
            $this->getPackageDimensionValue(
                \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_LENGTH,
                $magentoProduct
            )
        );
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotFoundAttributeValueException
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotConfiguredException
     */
    private function getPackageWidth(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): string
    {
        return (string)$this->toInteger(
            $this->getPackageDimensionValue(
                \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_WIDTH,
                $magentoProduct
            )
        );
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotConfiguredException
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotFoundAttributeValueException
     */
    private function getPackageHeight(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): string
    {
        return (string)$this->toInteger(
            $this->getPackageDimensionValue(
                \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_HEIGHT,
                $magentoProduct
            )
        );
    }

    private function getUnit(\M2E\TikTokShop\Model\Product $product): string
    {
        return $product->getShop()->getRegion()->getSizeDimension();
    }
}
