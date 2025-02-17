<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m10;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddColumnsToOrderItem extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_ITEM);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_SHIPPING_IN_PROGRESS,
            'SMALLINT UNSIGNED',
            \M2E\TikTokShop\Model\Order\Item::SHIPPING_IS_IN_PROGRESS_NO,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_ITEM_STATUS,
            'SMALLINT UNSIGNED',
            \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_UNKNOWN,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
