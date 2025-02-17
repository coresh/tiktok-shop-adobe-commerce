<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m09;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class ResetInventoryLastSyncDateInShop extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->getConnection()->update(
            $this->getFullTableName(Tables::TABLE_NAME_SHOP),
            [\M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_INVENTORY_LAST_SYNC => new \Zend_Db_Expr('NULL')],
        );
    }
}
