<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Listing\Wizard;

/**
 * @method \M2E\TikTokShop\Model\Listing\Wizard[] getItems()
 * @method \M2E\TikTokShop\Model\Listing\Wizard getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Listing\Wizard::class,
            \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::class
        );
    }
}
