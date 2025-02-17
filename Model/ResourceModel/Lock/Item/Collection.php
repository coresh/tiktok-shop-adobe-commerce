<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Lock\Item;

/**
 * @method \M2E\TikTokShop\Model\Lock\Item getFirstItem()
 * @method \M2E\TikTokShop\Model\Lock\Item[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Model\Lock\Item::class,
            \M2E\TikTokShop\Model\ResourceModel\Lock\Item::class
        );
    }
}
