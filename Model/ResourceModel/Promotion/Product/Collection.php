<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Promotion\Product;

/**
 * @method \M2E\TikTokShop\Model\Promotion\Product[] getItems()
 * @method \M2E\TikTokShop\Model\Promotion\Product getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Promotion\Product::class,
            \M2E\TikTokShop\Model\ResourceModel\Promotion\Product::class
        );
    }
}
