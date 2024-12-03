<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku;

/**
 * @method \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku getFirstItem()
 * @method \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();

        $this->_init(
            \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku::class,
            \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku::class
        );
    }
}
