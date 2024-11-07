<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Order\Item;

/**
 * @method \M2E\TikTokShop\Model\Order\Item[] getItems()
 * @method \M2E\TikTokShop\Model\Order\Item getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Order\Item::class,
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::class
        );
    }
}
