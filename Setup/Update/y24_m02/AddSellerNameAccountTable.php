<?php

namespace M2E\TikTokShop\Setup\Update\y24_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\Account as AccountResource;

class AddSellerNameAccountTable extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ACCOUNT);
        $modifier->addColumn(
            AccountResource::COLUMN_SELLER_NAME,
            'VARCHAR(255)',
            null,
            AccountResource::COLUMN_OPEN_ID,
        );
    }
}
