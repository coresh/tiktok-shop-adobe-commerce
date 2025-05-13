<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m03;

class AddAuditFailedReasonsColumnToProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT
        );

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_AUDIT_FAILED_REASONS,
            'LONGTEXT',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
