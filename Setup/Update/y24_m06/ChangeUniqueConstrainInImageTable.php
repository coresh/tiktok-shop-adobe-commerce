<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m06;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class ChangeUniqueConstrainInImageTable extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $imageTableName = $this->getFullTableName(Tables::TABLE_NAME_IMAGE);

        $this->getConnection()->dropIndex($imageTableName, 'hash');

        $this->getConnection()->addIndex(
            $imageTableName,
            'hash__type',
            [
                \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_HASH,
                \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_TYPE,
            ],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
