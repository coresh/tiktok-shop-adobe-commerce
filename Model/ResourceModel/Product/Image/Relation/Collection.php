<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation;

/**
 * @method \M2E\TikTokShop\Model\Product\Image\Relation[] getItems()
 * @method \M2E\TikTokShop\Model\Product\Image\Relation getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Product\Image\Relation::class,
            \M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation::class
        );
    }
}
