<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class CategoryValidator implements ValidatorInterface
{
    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
    ): ?ValidatorMessage {
        if (!$configurator->isCategoriesAllowed()) {
            return null;
        }

        if (!$product->hasCategoryTemplate()) {
            return new ValidatorMessage(
                'Categories Settings are not set',
                \M2E\TikTokShop\Model\Tag\ValidatorIssues::ERROR_CATEGORIES_NOT_SET
            );
        }

        return null;
    }
}
