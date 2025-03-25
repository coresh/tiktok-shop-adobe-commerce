<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration;

/**
 * @method \M2E\TikTokShop\Model\ManufacturerConfiguration getFirstItem()
 * @method \M2E\TikTokShop\Model\ManufacturerConfiguration[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\ManufacturerConfiguration::class,
            \M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration::class
        );
    }
}
