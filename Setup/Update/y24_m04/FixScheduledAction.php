<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m04;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class FixScheduledAction extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{

    public function execute(): void
    {
        $this->copyScheduledActionToInstruction();
        $this->deleteAllScheduledAction();
    }

    private function copyScheduledActionToInstruction(): void
    {
        $select = $this
            ->getConnection()
            ->select()
            ->from($this->getFullTableName(Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION));

        $stmt = $select->query();

        $insertData = [];
        while ($row = $stmt->fetch()) {
            $insertData[] = [
                'listing_product_id' => $row['listing_product_id'],
                'type' => \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
                'initiator' => 'variant_migration',
                'priority' => 100,
                'create_date' => \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
            ];
        }

        if (empty($insertData)) {
            return;
        }

        $this->getConnection()
             ->insertMultiple(
                 $this->getFullTableName(Tables::TABLE_NAME_PRODUCT_INSTRUCTION),
                 $insertData
             );
    }

    private function deleteAllScheduledAction(): void
    {
        $this
            ->getConnection()
            ->delete($this->getFullTableName(Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION));
    }
}
