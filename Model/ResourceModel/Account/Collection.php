<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Account;

/**
 * @method \M2E\TikTokShop\Model\Account[] getItems()
 * @method \M2E\TikTokShop\Model\Account getFirstItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();

        $this->_init(
            \M2E\TikTokShop\Model\Account::class,
            \M2E\TikTokShop\Model\ResourceModel\Account::class
        );
    }
}
