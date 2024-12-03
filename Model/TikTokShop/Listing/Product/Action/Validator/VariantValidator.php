<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

class VariantValidator
{
    protected const VARIATION_COUNT_MAXIMUM = 100;

    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku\PriceValidator $priceValidator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku\QtyValidator $qtyValidator;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku\PriceValidator $priceValidator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku\QtyValidator $qtyValidator
    ) {
        $this->priceValidator = $priceValidator;
        $this->qtyValidator = $qtyValidator;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): array {
        $variantsWithoutSkipped = [];
        $messages = [];

        foreach ($product->getVariants() as $variant) {
            if (
                $variantSettings->isSkipAction($variant->getId())
                || $variantSettings->isStopAction($variant->getId())
            ) {
                continue;
            }

            $variantsWithoutSkipped[] = $variant;
        }

        if (count($variantsWithoutSkipped) > self::VARIATION_COUNT_MAXIMUM) {
            $messages[] = sprintf(
                'The number of product variations cannot exceed %s.',
                self::VARIATION_COUNT_MAXIMUM
            );

            return $messages;
        }

        foreach ($variantsWithoutSkipped as $variant) {
            $variantHasError = false;

            if ($error = $this->priceValidator->validate($variant)) {
                $messages[] = $error;
                $variantHasError = true;
            }

            if ($error = $this->qtyValidator->validate($variant)) {
                $messages[] = $error;
                $variantHasError = true;
            }

            if ($variantHasError) {
                return $messages;
            }
        }

        return [];
    }
}
