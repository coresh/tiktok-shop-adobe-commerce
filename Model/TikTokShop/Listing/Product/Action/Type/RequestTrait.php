<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

trait RequestTrait
{
    private function generateItemId(\M2E\TikTokShop\Model\Product $product): string
    {
        return 'item_id_' . $product->getId();
    }
}
