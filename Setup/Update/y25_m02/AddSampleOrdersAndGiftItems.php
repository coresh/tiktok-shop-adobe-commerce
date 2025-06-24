<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Order as OrderResource;
use M2E\TikTokShop\Model\ResourceModel\Order\Item as OrderItemResource;
use Magento\Framework\DB\Ddl\Table;

class AddSampleOrdersAndGiftItems extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_ORDER);

        $modifier->addColumn(
            OrderResource::COLUMN_IS_SAMPLE,
            Table::TYPE_SMALLINT,
            0,
            null,
            false,
            false
        );
        $modifier->commit();

        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_ORDER_ITEM);

        $modifier->addColumn(
            OrderItemResource::COLUMN_IS_GIFT,
            Table::TYPE_SMALLINT,
            0,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
