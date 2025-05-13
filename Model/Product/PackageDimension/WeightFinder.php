<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\PackageDimension;

class WeightFinder extends AbstractDimensionFinder
{
    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException
     */
    public function find(\M2E\TikTokShop\Model\Product $product): Weight
    {
        $unit = $this->getUnit($product);

        try {
            return new Weight(
                $this->getPackageWeight($product->getMagentoProduct()),
                $unit
            );
        } catch (NotConfiguredException $exception) {
            throw $exception;
        } catch (PackageDimensionException $exception) {
        }

        $childWeights = [];
        foreach ($product->getVariants() as $variant) {
            try {
                $childWeight = new Weight(
                    $this->getPackageWeight($variant->getMagentoProduct()),
                    $unit
                );
                $childWeights[$childWeight->getValue()] = $childWeight;
            } catch (PackageDimensionException $exception) {
                continue;
            }
        }

        if (count($childWeights) === 0) {
            throw new PackageDimensionException(
                (string)__('Package Weight is missing. To list the Product, please make sure that the Package settings are correct.'),
                [],
                \M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException::CODE_WEIGHT_MISSING
            );
        }

        krsort($childWeights, SORT_NUMERIC);

        return reset($childWeights);
    }

    private function getUnit(\M2E\TikTokShop\Model\Product $product): string
    {
        return $product->getShop()->getRegion()->getWeightDimension();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotConfiguredException
     * @throws \M2E\TikTokShop\Model\Product\PackageDimension\NotFoundAttributeValueException
     */
    public function getPackageWeight(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): string
    {
        return (string)$this->toFloat(
            $this->getPackageDimensionValue(
                \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration::DIMENSION_TYPE_WEIGHT,
                $magentoProduct
            )
        );
    }
}
