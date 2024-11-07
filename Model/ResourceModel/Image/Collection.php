<?php

namespace M2E\TikTokShop\Model\ResourceModel\Image;

/**
 * @method \M2E\TikTokShop\Model\Image[] getItems()
 * @method \M2E\TikTokShop\Model\Image getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Image::class,
            \M2E\TikTokShop\Model\ResourceModel\Image::class
        );
    }
}
