<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class ImagesValidator implements ValidatorInterface
{
    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
    ): ?string {
        if (!$configurator->isImagesAllowed()) {
            return null;
        }

        $images = $product
            ->getDescriptionTemplateSource()
            ->getImageSet()
            ->getAll();

        if (count($images) <= 0) {
            return 'Product Images are missing. To list the Product, ' .
                'please make sure that the Image settings in the Description policy are correct and the Images ' .
                'are available in the Magento Product.';
        }

        return null;
    }
}
