<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Instruction extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_PRODUCT_ID = 'listing_product_id';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_INITIATOR = 'initiator';
    public const COLUMN_PRIORITY = 'priority';
    public const COLUMN_SKIP_UNTIL = 'skip_until';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_INSTRUCTION,
            self::COLUMN_ID
        );
    }
}
