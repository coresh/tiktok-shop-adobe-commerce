<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop;

class Request extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\AbstractRequest
{
    public function getActionData(): array
    {
        return [
            'product_ids' => [$this->getListingProduct()->getTTSProductId()],
            'shop_id' => $this->getListingProduct()->getListing()->getShop()->getShopId(),
        ];
    }
}
