<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku;

/**
 * @method \M2E\TikTokShop\Model\GlobalProduct\VariantSku getFirstItem()
 * @method \M2E\TikTokShop\Model\GlobalProduct\VariantSku[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();

        $this->_init(
            \M2E\TikTokShop\Model\GlobalProduct\VariantSku::class,
            \M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku::class
        );
    }
}
