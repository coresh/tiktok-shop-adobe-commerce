<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Template\SellingFormat::class,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::class
        );
    }
}
