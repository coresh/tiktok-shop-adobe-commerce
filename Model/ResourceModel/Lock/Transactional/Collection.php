<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Lock\Transactional;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Model\Lock\Transactional::class,
            \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::class
        );
    }
}
