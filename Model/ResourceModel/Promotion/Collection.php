<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Promotion;

/**
 * @method \M2E\TikTokShop\Model\Promotion[] getItems()
 * @method \M2E\TikTokShop\Model\Promotion getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Promotion::class,
            \M2E\TikTokShop\Model\ResourceModel\Promotion::class
        );
    }
}
