<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Instruction;

/**
 * @method \M2E\TikTokShop\Model\Instruction[] getItems()
 */
class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Model\Instruction::class,
            \M2E\TikTokShop\Model\ResourceModel\Instruction::class
        );
    }
}
