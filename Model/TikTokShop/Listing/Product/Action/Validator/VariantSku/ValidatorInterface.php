<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku;

interface ValidatorInterface
{
    public function validate(\M2E\TikTokShop\Model\Product\VariantSku $variant): ?string;
}
