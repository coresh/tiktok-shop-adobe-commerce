<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup;

class MigrateToCore
{
    private const SETUP_TABLE_NAME = 'm2e_tts_setup';

    private \M2E\Core\Helper\Module\Database\Structure $databaseStructure;
    private \M2E\Core\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(
        \M2E\Core\Helper\Module\Database\Structure $databaseStructure,
        \M2E\Core\Helper\Module\Database\Tables $tablesHelper
    ) {
        $this->databaseStructure = $databaseStructure;
        $this->tablesHelper = $tablesHelper;
    }

    public function isNeedMigrate(): bool
    {
        return $this->databaseStructure
            ->isTableExists($this->tablesHelper->getFullName(self::SETUP_TABLE_NAME));
    }

    public function migrate(\Magento\Framework\DB\Adapter\AdapterInterface $connection): void
    {
        $oldTable = $this->tablesHelper->getFullName(self::SETUP_TABLE_NAME);
        $newTable = $this->tablesHelper->getFullName(\M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_SETUP);

        $oldTableRows = $this->fetchTableRows($connection, $oldTable);

        $newTableRows = $this->fetchTableRows(
            $connection,
            $newTable,
            \M2E\TikTokShop\Helper\Module::IDENTIFIER
        );

        $newTableRowsByKey = [];
        foreach ($newTableRows as $row) {
            $key = $this->makeKey((string)$row['version_from'], (string)$row['version_to']);
            $newTableRowsByKey[$key] = $row;
        }

        foreach ($oldTableRows as $oldRow) {
            $key = $this->makeKey((string)$oldRow['version_from'], (string)$oldRow['version_to']);

            if (!isset($newTableRowsByKey[$key])) {
                $connection->insert($newTable, [
                    'extension_name' => \M2E\TikTokShop\Helper\Module::IDENTIFIER,
                    'version_from' => $oldRow['version_from'],
                    'version_to' => $oldRow['version_to'],
                    'is_completed' => $oldRow['is_completed'],
                    'profiler_data' => $oldRow['profiler_data'],
                    'update_date' => $oldRow['update_date'],
                    'create_date' => $oldRow['create_date'],
                ]);
            }
        }

        $connection->dropTable($oldTable);
    }

    private function fetchTableRows(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        string $table,
        ?string $extensionName = null
    ): array {
        $select = $connection->select()->from($table);

        if ($extensionName !== null) {
            $select->where('extension_name = ?', $extensionName);
        }

        return $connection->fetchAll($select);
    }

    private function makeKey(string $from, string $to): string
    {
        return $from . '-' . $to;
    }
}
