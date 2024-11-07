<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m05;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;

class DropProductBackupTable extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $backupTableName = $this->getFullTableName(TablesHelper::PREFIX . 'backup_product');
        $this->getConnection()->dropTable($backupTableName);
    }
}
