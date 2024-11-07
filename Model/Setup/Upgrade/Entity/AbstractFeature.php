<?php

namespace M2E\TikTokShop\Model\Setup\Upgrade\Entity;

use M2E\TikTokShop\Model\Setup\Database\Modifier\Table;
use M2E\TikTokShop\Model\Setup\Database\Modifier\Config;

abstract class AbstractFeature
{
    private \Magento\Framework\Module\Setup $installer;
    private \M2E\TikTokShop\Model\Setup\Database\Modifier\TableFactory $modifierTableFactory;
    private \M2E\TikTokShop\Model\Setup\Database\Modifier\ConfigFactory $modifierConfigFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Setup\Database\Modifier\ConfigFactory $modifierConfigFactory,
        \M2E\TikTokShop\Model\Setup\Database\Modifier\TableFactory $modifierTableFactory,
        \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper,
        \Magento\Framework\Module\Setup $installer
    ) {
        $this->installer = $installer;
        $this->modifierTableFactory = $modifierTableFactory;
        $this->modifierConfigFactory = $modifierConfigFactory;
        $this->tablesHelper = $tablesHelper;
    }

    // ----------------------------------------

    abstract public function execute(): void;

    // ----------------------------------------

    protected function createTableModifier(string $tableName): Table
    {
        return $this->modifierTableFactory->create(
            $tableName,
            $this->installer
        );
    }

    protected function getConfigModifier(string $configName = ''): Config
    {
        $tableName = $configName . '_config';
        if ($this->getConnection()->isTableExists($this->getFullTableName('config'))) {
            $tableName = 'config';
        }

        return $this->modifierConfigFactory->create($tableName, $this->installer);
    }

    // ----------------------------------------

    /**
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected function getConnection()
    {
        return $this->installer->getConnection();
    }

    protected function getFullTableName(string $tableName): string
    {
        return $this->tablesHelper->getFullName($tableName);
    }

    // ----------------------------------------

    public function renameTable(string $oldTable, string $newTable): bool
    {
        return $this->tablesHelper->renameTable($oldTable, $newTable);
    }
}
