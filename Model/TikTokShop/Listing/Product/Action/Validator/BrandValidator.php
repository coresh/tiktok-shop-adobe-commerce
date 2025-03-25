<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class BrandValidator implements ValidatorInterface
{
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\Mapper $manufacturerConfigurationMapper;

    public function __construct(
        \M2E\TikTokShop\Model\ManufacturerConfiguration\Mapper $manufacturerConfigurationMapper
    ) {
        $this->manufacturerConfigurationMapper = $manufacturerConfigurationMapper;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
    ): ?string {
        if (
            !$product->getCategoryDictionary()->isRequiredManufacturer()
            && !$product->getCategoryDictionary()->isRequiredResponsiblePerson()
        ) {
            return null;
        }

        if ($product->getManufacturerConfigId() !== null) {
            return null;
        }

        $mapperResult = $this->manufacturerConfigurationMapper->execute($product);
        if ($mapperResult->isFail()) {
            return $mapperResult->getFailMessage();
        }

        return null;
    }
}
