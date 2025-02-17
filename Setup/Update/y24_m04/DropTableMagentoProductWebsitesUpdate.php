<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class DropTableMagentoProductWebsitesUpdate extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $tableName = $this->getFullTableName(Tables::PREFIX . 'magento_product_websites_update');

        $this->getConnection()
             ->dropTable($tableName);
    }
}
