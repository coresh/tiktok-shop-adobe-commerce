<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m05;

class UpdateProductStatus extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    private const PRODUCT_STATUS_STOPPED = 3;

    public function execute(): void
    {
        $this->getConnection()
             ->update(
                 $this->getFullTableName(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT),
                 ['status' => 8], // \M2E\TikTokShop\Model\Product::STATUS_INACTIVE
                 ['status = ?' => self::PRODUCT_STATUS_STOPPED],
             );
    }
}
