<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorMessage;

class PriceValidator implements ValidatorInterface
{
    public function validate(\M2E\TikTokShop\Model\Product\VariantSku $variant): ?ValidatorMessage
    {
        return $this->validateByRegion(
            $variant->getListing()->getShop()->getRegion(),
            $variant->getFixedPrice()
        );
    }

    private function validateByRegion(\M2E\TikTokShop\Model\Shop\Region $region, float $price): ?ValidatorMessage
    {
        $validatorData = $region->getProductPriceRestrictions();

        if (
            $price < $validatorData->getMinProductPrice()
            || $price > $validatorData->getMaxProductPrice()
        ) {
            return new ValidatorMessage(
                sprintf(
                    'The product price must be between %1$s %3$s and %2$s %3$s.',
                    $validatorData->getMinProductPrice(),
                    $validatorData->getMaxProductPrice(),
                    $region->getCurrency()
                ),
                \M2E\TikTokShop\Model\Tag\ValidatorIssues::ERROR_PRICE_OUT_OF_RANGE
            );
        }

        return null;
    }
}
