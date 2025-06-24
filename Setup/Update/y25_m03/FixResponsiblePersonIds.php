<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m03;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\Product;

class FixResponsiblePersonIds extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->changeComplianceColumnResponsiblePersonIds();
        $this->changeProductColumnResponsiblePersonIds();
    }

    private function changeComplianceColumnResponsiblePersonIds(): void
    {
        $modifier = $this->createTableModifier(
            Tables::PREFIX . 'template_compliance'
        );

        $modifier->changeColumn(
            'responsible_person_ids',
            'LONGTEXT',
            null,
            null,
            false
        );

        $modifier->commit();
    }

    private function changeProductColumnResponsiblePersonIds(): void
    {
        $modifier = $this->createTableModifier(
            Tables::TABLE_NAME_PRODUCT
        );

        $modifier->changeColumn(
            Product::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS,
            'LONGTEXT',
            null,
            null,
            false
        );

        $modifier->commit();
    }
}
