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
    ): ?ValidatorMessage {
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
            return new ValidatorMessage(
                $mapperResult->getFailMessage(),
                $this->mapErrorCode($mapperResult->getCode())
            );
        }

        return null;
    }

    private function mapErrorCode(int $mapperErrorCode): string
    {
        switch ($mapperErrorCode) {
            case \M2E\TikTokShop\Model\ManufacturerConfiguration\Mapper::BRAND_MISSING:
                return \M2E\TikTokShop\Model\Tag\ValidatorIssues::ERROR_MISSING_BRAND;

            case \M2E\TikTokShop\Model\ManufacturerConfiguration\Mapper::GPSR_MISSING:
                return \M2E\TikTokShop\Model\Tag\ValidatorIssues::ERROR_MISSING_GPSR_INFO;

            default:
                return \M2E\TikTokShop\Model\Tag\ValidatorIssues::NOT_USER_ERROR;
        }
    }
}
