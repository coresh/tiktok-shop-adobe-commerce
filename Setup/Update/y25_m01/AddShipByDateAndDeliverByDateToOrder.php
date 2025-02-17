<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m01;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Order as OrderResource;
use Magento\Framework\DB\Ddl\Table;

class AddShipByDateAndDeliverByDateToOrder extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createShipByDateColumn();
        $this->changeColumnShippingDateTo();
    }

    private function createShipByDateColumn()
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_ORDER);

        $modifier->addColumn(
            OrderResource::COLUMN_SHIP_BY_DATE,
            Table::TYPE_DATETIME,
            null,
            null,
            true,
            false
        );

        $modifier->commit();
    }

    private function changeColumnShippingDateTo(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_ORDER);
        $modifier->renameColumn('shipping_date_to', OrderResource::COLUMN_DELIVER_BY_DATE);
    }
}
