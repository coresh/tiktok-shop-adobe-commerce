<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class Validator implements \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ValidatorInterface
{
    use Action\Type\ValidatorTrait;

    private Action\Validator\TitleValidator $titleValidator;
    private Action\Validator\ImagesValidator $imagesValidator;
    private Action\Validator\CategoryValidator $categoryValidator;
    private Action\Validator\PackageWeightValidator $packageWeightValidator;
    private Action\Validator\PackageSizeValidator $packageSizeValidator;
    private Action\Validator\VariantValidator $variantValidator;

    public function __construct(
        Action\Validator\TitleValidator $titleValidator,
        Action\Validator\ImagesValidator $imagesValidator,
        Action\Validator\CategoryValidator $categoryValidator,
        Action\Validator\PackageWeightValidator $packageWeightValidator,
        Action\Validator\PackageSizeValidator $packageSizeValidator,
        Action\Validator\VariantValidator $variantValidator
    ) {
        $this->titleValidator = $titleValidator;
        $this->imagesValidator = $imagesValidator;
        $this->categoryValidator = $categoryValidator;
        $this->packageWeightValidator = $packageWeightValidator;
        $this->packageSizeValidator = $packageSizeValidator;
        $this->variantValidator = $variantValidator;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $actionConfigurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): bool {
        if (!$product->isListable()) {
            $this->addErrorMessage((string)__('Item is Listed or not available'));

            return false;
        }

        if (!$actionConfigurator->isVariantsAllowed()) {
            $this->addErrorMessage((string)__('The product was not listed because it has no associated products.'));

            return false;
        }

        $this->validateProductBy(
            $product,
            $actionConfigurator,
            [
                $this->categoryValidator,
                $this->titleValidator,
                $this->imagesValidator,
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
