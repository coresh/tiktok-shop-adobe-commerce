<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Log\System;

/**
 * @method \M2E\TikTokShop\Model\Log\System getFirstItem()
 * @method \M2E\TikTokShop\Model\Log\System[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Log\System::class,
            \M2E\TikTokShop\Model\ResourceModel\Log\System::class
        );
    }
}
