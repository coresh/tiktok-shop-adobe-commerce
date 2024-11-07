<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class PackageWeightValidator implements ValidatorInterface
{
    private \M2E\TikTokShop\Model\Product\PackageDimensionFinder $packageDimensionFinder;

    public function __construct(
        \M2E\TikTokShop\Model\Product\PackageDimensionFinder $packageDimensionFinder
    ) {
        $this->packageDimensionFinder = $packageDimensionFinder;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
    ): ?string {
        try {
            $weight = $this->packageDimensionFinder->getWeight($product);
        } catch (\M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException $exception) {
            return $exception->getMessage();
        }

        return $this->validateByRegion(
            $product->getShop()->getRegion(),
            (float)$weight->getValue()
        );
    }

    /**
     * The product package weight limit cannot exceed 150 lb (for US) or 30 kg (for EU)
     */
    private function validateByRegion(string $regionCode, float $weight): ?string
    {
        $validatorDataMap = [
            \M2E\TikTokShop\Model\Shop::REGION_US => ['max' => 150, 'unit' => 'lb'],
            \M2E\TikTokShop\Model\Shop::REGION_GB => ['max' => 30, 'unit' => 'kg'],
            \M2E\TikTokShop\Model\Shop::REGION_ES => ['max' => 30, 'unit' => 'kg'],
        ];

        $validatorData = $validatorDataMap[$regionCode] ?? null;
        if ($validatorData === null) {
            return null;
        }

        if ($weight > $validatorData['max']) {
            return sprintf(
                'The product package weight must be within %s %s.',
                $validatorData['max'],
                $validatorData['unit']
            );
        }

        return null;
    }
}
