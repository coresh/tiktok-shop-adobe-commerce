<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class Validator implements \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ValidatorInterface
{
    use Action\Type\ValidatorTrait;

    private Action\Validator\TitleValidator $titleValidator;
    private Action\Validator\PackageWeightValidator $packageWeightValidator;
    private Action\Validator\PackageSizeValidator $packageSizeValidator;
    private Action\Validator\VariantValidator $variantValidator;

    public function __construct(
        Action\Validator\TitleValidator $titleValidator,
        Action\Validator\PackageWeightValidator $packageWeightValidator,
        Action\Validator\PackageSizeValidator $packageSizeValidator,
        Action\Validator\VariantValidator $variantValidator
    ) {
        $this->titleValidator = $titleValidator;
        $this->packageWeightValidator = $packageWeightValidator;
        $this->packageSizeValidator = $packageSizeValidator;
        $this->variantValidator = $variantValidator;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $actionConfigurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): bool {
        if (!$product->isRevisable()) {
            $this->addErrorMessage('Item is not Listed or not available');

            return false;
        }

        if (empty($product->getTTSProductId())) {
            return false;
        }

        $this->validateProductBy(
            $product,
            $actionConfigurator,
            [
                $this->titleValidator,
                $this->packageSizeValidator,
                $this->packageWeightValidator,
            ]
        );

        $variantErrors = $this->variantValidator->validate($product, $variantSettings);
        foreach ($variantErrors as $variantError) {
            $this->addErrorMessage($variantError);
        }

        return !$this->hasErrorMessages();
    }
}
