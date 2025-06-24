<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m01;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\Product;
use Magento\Framework\DB\Ddl\Table;

class ChangeComplianceAndProductTables extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->changeComplianceColumnResponsiblePersonIds();
        $this->changeProductColumnResponsiblePersonIds();

        $this->updateComplianceResponsiblePersonIdsColumn();
        $this->updateProductResponsiblePersonIdsColumn();
    }

    private function changeComplianceColumnResponsiblePersonIds(): void
    {
        $modifier = $this->createTableModifier(
            Tables::PREFIX . 'template_compliance'
        );

        $modifier->renameColumn('responsible_person_id', 'responsible_person_ids');

        $modifier->changeColumn(
            'responsible_person_ids',
            Table::TYPE_TEXT,
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

        $modifier->renameColumn('online_responsible_person_id', Product::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS);

        $modifier->changeColumn(
            Product::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS,
            Table::TYPE_TEXT,
            null,
            null,
            false
        );

        $modifier->commit();
    }

    private function updateComplianceResponsiblePersonIdsColumn(): void
    {
        $templateComplianceTableName = $this->getFullTableName(Tables::PREFIX . 'template_compliance');
        $select = $this
            ->getConnection()
            ->select()
            ->from($templateComplianceTableName);

        $stmt = $select->query();

        while ($row = $stmt->fetch()) {
            $responsiblePersonIdsData = $row['responsible_person_ids'] ?? null;
            if ($responsiblePersonIdsData === null) {
                continue;
            }

            $responsiblePersonIdsData = json_encode([$responsiblePersonIdsData]);

            $this->getConnection()->update(
                $templateComplianceTableName,
                ['responsible_person_ids' => $responsiblePersonIdsData],
                "id = {$row['id']}"
            );
        }
    }

    private function updateProductResponsiblePersonIdsColumn(): void
    {
        $productTableName = $this->getFullTableName(Tables::TABLE_NAME_PRODUCT);
        $select = $this
            ->getConnection()
            ->select()
            ->from($productTableName);

        $stmt = $select->query();

        while ($row = $stmt->fetch()) {
            $responsiblePersonIdsData = $row[Product::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS] ?? null;
            if ($responsiblePersonIdsData === null) {
                continue;
            }

            $responsiblePersonIdsData = json_encode([$responsiblePersonIdsData]);

            $this->getConnection()->update(
                $productTableName,
                [Product::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS => $responsiblePersonIdsData],
                "id = {$row['id']}"
            );
        }
    }
}
