<?php

namespace M2E\TikTokShop\Model\Setup\Database\Modifier;

use Magento\Framework\DB\Adapter\AdapterInterface;

class AbstractModifier
{
    protected \Magento\Framework\Setup\SetupInterface $installer;
    protected AdapterInterface $connection;
    /** @var string */
    protected string $tableName;

    public function __construct(
        \Magento\Framework\Setup\SetupInterface $installer,
        string $tableName
    ) {
        $this->tableName = $tableName;
        $this->installer = $installer;
        $this->connection = $installer->getConnection();
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function runQuery($query)
    {
        $this->connection->query($query);
        $this->connection->resetDdlCache();

        return $this;
    }
}
