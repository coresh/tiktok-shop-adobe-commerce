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

    private function validateByRegion(string $regionCode, float $price): ?string
    {
        $validatorDataMap = [
            \M2E\TikTokShop\Model\Shop::REGION_US => ['min' => 0.01, 'max' => 7600],
            \M2E\TikTokShop\Model\Shop::REGION_GB => ['min' => 0.01, 'max' => 5600],
            \M2E\TikTokShop\Model\Shop::REGION_ES => ['min' => 0.01, 'max' => 5600],
        ];

        $validatorData = $validatorDataMap[$regionCode] ?? null;
        if ($validatorData === null) {
            return null;
        }

        $currency = \M2E\TikTokShop\Model\Shop::getCurrencyCodeByRegion($regionCode);

        if ($price < $validatorData['min'] || $price > $validatorData['max']) {
            return sprintf(
                'The product price must be between %1$s %3$s and %2$s %3$s.',
                $validatorData['min'],
                $validatorData['max'],
                $currency
            );
        }

        return null;
    }
}
