<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku;

class PriceValidator implements ValidatorInterface
{
    public function validate(\M2E\TikTokShop\Model\Product\VariantSku $variant): ?string
    {
        return $this->validateByRegion(
            $variant->getWarehouse()->getShop()->getRegion(),
            $variant->getFixedPrice()
        );
    }

    private function validateByRegion(\M2E\TikTokShop\Model\Shop\Region $region, float $price): ?string
    {
        $validatorData = $region->getProductPriceRestrictions();

        if (
            $price < $validatorData->getMinProductPrice()
            || $price > $validatorData->getMaxProductPrice()
        ) {
            return sprintf(
                'The product price must be between %1$s %3$s and %2$s %3$s.',
                $validatorData->getMinProductPrice(),
                $validatorData->getMaxProductPrice(),
                $region->getCurrency()
            );
        }

        return null;
    }
}
