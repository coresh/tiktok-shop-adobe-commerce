<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class CategoryValidator implements ValidatorInterface
{
    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
    ): ?string {
        if (!$configurator->isCategoriesAllowed()) {
            return null;
        }

        if (!$product->hasCategoryTemplate()) {
            return 'Categories Settings are not set';
        }

        return null;
    }
}
