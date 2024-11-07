<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Database;

class TableModelFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $tableName): TableModel
    {
        return $this->objectManager->create(TableModel::class, ['tableName' => $tableName]);
    }
}
