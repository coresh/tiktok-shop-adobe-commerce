<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m10;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class AddSupportCPF extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_CPF,
            'VARCHAR(255)',
            null,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_CPF_NAME,
            'VARCHAR(255)',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
