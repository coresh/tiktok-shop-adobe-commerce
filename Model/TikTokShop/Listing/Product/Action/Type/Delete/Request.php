<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Delete;

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
