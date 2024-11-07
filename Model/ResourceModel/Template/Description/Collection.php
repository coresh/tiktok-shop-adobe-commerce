<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template\Description;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Template\Description::class,
            \M2E\TikTokShop\Model\ResourceModel\Template\Description::class
        );
    }
}
