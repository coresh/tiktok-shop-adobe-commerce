<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Warehouse;

/**
 * @method \M2E\TikTokShop\Model\Warehouse getFirstItem()
 * @method \M2E\TikTokShop\Model\Warehouse[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Warehouse::class,
            \M2E\TikTokShop\Model\ResourceModel\Warehouse::class
        );
    }
}
