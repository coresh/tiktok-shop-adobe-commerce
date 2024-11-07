<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Setup;

/**
 * @method \M2E\TikTokShop\Model\Setup getFirstItem()
 * @method \M2E\TikTokShop\Model\Setup[] getItems()
 * @method \M2E\TikTokShop\Model\Setup getLastItem()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Model\Setup::class,
            \M2E\TikTokShop\Model\ResourceModel\Setup::class
        );
    }
}
