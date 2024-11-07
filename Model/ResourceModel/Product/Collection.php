<?php

namespace M2E\TikTokShop\Model\ResourceModel\Product;

/**
 * @method \M2E\TikTokShop\Model\Product getFirstItem()
 * @method \M2E\TikTokShop\Model\Product[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Product::class,
            \M2E\TikTokShop\Model\ResourceModel\Product::class
        );
    }
}
