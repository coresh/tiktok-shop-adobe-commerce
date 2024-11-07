<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Order\Change;

/**
 * @method \M2E\TikTokShop\Model\Order\Change[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Order\Change::class,
            \M2E\TikTokShop\Model\ResourceModel\Order\Change::class,
        );
    }
}
