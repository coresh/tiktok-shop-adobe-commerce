<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m07;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddBuyerReturnRefundColumnsToOrderItem extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_ITEM);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_BUYER_REQUEST_REFUND_RETURN,
            'SMALLINT',
            '0',
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_TRACKING_DETAILS
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_CANCEL_REASON,
            'VARCHAR(255)',
            null,
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_BUYER_REQUEST_REFUND_RETURN
        );
    }
}
