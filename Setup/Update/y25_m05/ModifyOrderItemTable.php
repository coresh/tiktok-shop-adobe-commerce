<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m05;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class ModifyOrderItemTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->addColumns();
        $this->removeColumn();
    }

    private function addColumns(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_ITEM);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_BUYER_REQUEST_REFUND,
            'SMALLINT',
            '0',
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_TRACKING_DETAILS,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_BUYER_REQUEST_RETURN,
            'SMALLINT',
            '0',
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_BUYER_REQUEST_REFUND,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_REFUND_RETURN_ID,
            'VARCHAR(255)',
            'NULL',
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_BUYER_REQUEST_RETURN,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_REFUND_RETURN_STATUS,
            'VARCHAR(255)',
            'NULL',
            \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_REFUND_RETURN_ID,
            false,
            false
        );

        $modifier->commit();
    }

    public function removeColumn(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_ITEM);

        $modifier->dropColumn('buyer_request_refund_return')
                 ->commit();
    }
}
