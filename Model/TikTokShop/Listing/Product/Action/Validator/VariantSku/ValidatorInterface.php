<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\VariantSku;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorMessage;

interface ValidatorInterface
{
    public function validate(\M2E\TikTokShop\Model\Product\VariantSku $variant): ?ValidatorMessage;
}
