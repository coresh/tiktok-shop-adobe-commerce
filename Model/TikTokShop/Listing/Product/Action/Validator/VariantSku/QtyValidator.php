<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku;

class QtyValidator implements ValidatorInterface
{
    private const QTY_MAXIMUM = 999999;

    public function validate(\M2E\TikTokShop\Model\Product\VariantSku $variant): ?string
    {
        $qty = $variant->getQty();
        $clearQty = $variant->getMagentoProduct()->getQty(true);

        if ($clearQty > 0 && $qty <= 0) {
            return "You're submitting an item with QTY contradicting the QTY settings in your Selling Policy. " .
                'Please check Minimum Quantity to Be Listed and Quantity Percentage options.';
        }

        if ($qty > self::QTY_MAXIMUM) {
            return sprintf('Product QTY cannot exceed %s.', self::QTY_MAXIMUM);
        }

        return null;
    }
}
