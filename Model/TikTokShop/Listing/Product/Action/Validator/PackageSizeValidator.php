<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class PackageSizeValidator implements ValidatorInterface
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
            $this->packageDimensionFinder->getSize($product);
        } catch (\M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException $exception) {
            return $exception->getMessage();
        }

        return null;
    }
}
