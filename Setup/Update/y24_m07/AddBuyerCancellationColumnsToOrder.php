<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m07;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddBuyerCancellationColumnsToOrder extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_BUYER_CANCELLATION_REQUEST,
            'SMALLINT',
            '0',
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_BUYER_CANCELLATION_REQUEST_REASON,
            'VARCHAR(255)',
        );
    }
}
