<?php

namespace M2E\TikTokShop\Model\ResourceModel\Category\Tree;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Category\Tree::class,
            \M2E\TikTokShop\Model\ResourceModel\Category\Tree::class
        );
    }
}
