<?php

namespace M2E\TikTokShop\Model\ResourceModel\ShippingProvider;

use M2E\TikTokShop\Model\ResourceModel\ShippingProvider as ShippingProviderResource;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Model\ShippingProvider::class,
            ShippingProviderResource::class
        );
    }
}
