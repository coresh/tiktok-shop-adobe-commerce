<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m02;

class MigrateConfigToCore extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $connection = $this->getConnection();
        $oldTable = $this->getFullTableName('m2e_tts_config');
        $coreConfig = $this->getConfigModifier(\M2E\TikTokShop\Helper\Module::IDENTIFIER);

        if (!$connection->isTableExists($oldTable)) {
            return;
        }

        $configRows = $connection->fetchAll(
            $connection->select()->from($oldTable)
        );

        foreach ($configRows as $row) {
            $oldConfigValue = $row['value'] ?? null;

            if ($oldConfigValue === null) {
                continue;
            }

            $newConfig = $coreConfig->getEntity($row['group'], $row['key']);

            if ($newConfig->getValue() !== null) {
                continue;
            }

            $newConfig->insert($oldConfigValue);
        }

        $this->getConnection()->dropTable($oldTable);
    }
}
