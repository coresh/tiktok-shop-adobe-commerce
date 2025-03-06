<?php

namespace M2E\TikTokShop\Model\ResourceModel\GlobalProduct;

/**
 * @method \M2E\TikTokShop\Model\GlobalProduct getFirstItem()
 * @method \M2E\TikTokShop\Model\GlobalProduct[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\GlobalProduct::class,
            \M2E\TikTokShop\Model\ResourceModel\GlobalProduct::class
        );
    }
}
