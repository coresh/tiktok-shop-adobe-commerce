<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Order;

/**
 * @method \M2E\TikTokShop\Model\Order[] getItems()
 * @method \M2E\TikTokShop\Model\Order getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Order::class,
            \M2E\TikTokShop\Model\ResourceModel\Order::class
        );
    }
}
