<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m05;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class AddStatusChangerColumnToScheduledAction extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createColumn();
        $this->updateStatusChangerColumn();
    }

    private function createColumn(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);
        $modifier->addColumn(
            ScheduledActionResource::COLUMN_STATUS_CHANGER,
            'SMALLINT UNSIGNED NOT NULL',
            '0',
            ScheduledActionResource::COLUMN_ACTION_TYPE,
        );
    }

    private function updateStatusChangerColumn(): void
    {
        $scheduledActionTableName = $this->getFullTableName(Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);
        $select = $this
            ->getConnection()
            ->select()
            ->from($scheduledActionTableName);

        $stmt = $select->query();

        while ($row = $stmt->fetch()) {
            $additionalData = $row['additional_data'] ?? null;
            if ($additionalData === null) {
                continue;
            }

            $additionalData = json_decode($additionalData, true);
            $statusChanger = $additionalData['params']['status_changer'] ?? 0;

            if ($statusChanger === 0) {
                continue;
            }

            $this->getConnection()->update(
                $scheduledActionTableName,
                [ScheduledActionResource::COLUMN_STATUS_CHANGER => $statusChanger],
                "id = {$row['id']}"
            );
        }
    }
}
