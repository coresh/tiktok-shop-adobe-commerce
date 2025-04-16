<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class ProductPackage extends AbstractDataBuilder
{
    public const NICK = 'ProductPackage';

    private \M2E\TikTokShop\Model\Product\PackageDimensionFinder $packageDimensionFinder;

    public function __construct(
        \M2E\TikTokShop\Model\Product\PackageDimensionFinder $packageDimensionService,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->packageDimensionFinder = $packageDimensionService;
    }

    /**
     * @return array{
     *     weight: array{value:string, unit:string},
     *     dimensions: array{
     *          length: string,
     *          width: string,
     *          height: string,
     *          unit: string,
     *     }
     * }
     */
    public function getBuilderData(): array
    {
        $response = [
            'weight' => [],
            'dimensions' => [],
        ];

        try {
            $weight = $this->packageDimensionFinder->getWeight($this->getListingProduct());
            $response['weight']['value'] = $weight->getValue();
            $response['weight']['unit'] = $weight->getUnit();
        } catch (\M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException $exception) {
        }

        try {
            $size = $this->packageDimensionFinder->getSize($this->getListingProduct());
            $response['dimensions']['length'] = $size->getLength();
            $response['dimensions']['width'] = $size->getWidth();
            $response['dimensions']['height'] = $size->getHeight();
            $response['dimensions']['unit'] = $size->getUnit();
        } catch (\M2E\TikTokShop\Model\Product\PackageDimension\PackageDimensionException $exception) {
        }

        return $response;
    }
}
