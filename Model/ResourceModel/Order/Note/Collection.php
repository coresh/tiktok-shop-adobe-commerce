<?php

namespace M2E\TikTokShop\Model\ResourceModel\Order\Note;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public const ORDER_ID_FIELD = 'order_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Order\Note::class,
            \M2E\TikTokShop\Model\ResourceModel\Order\Note::class
        );
    }

    /**
     * @return \M2E\TikTokShop\Model\Order\Note[]
     */
    public function getItems()
    {
        /** @var \M2E\TikTokShop\Model\Order\Note[] $items */
        $items = parent::getItems();

        return $items;
    }
}
