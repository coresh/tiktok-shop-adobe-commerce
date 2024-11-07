<?php

namespace M2E\TikTokShop\Model\ControlPanel\Database;

use M2E\TikTokShop\Model\Exception;

class TableModel
{
    private string $tableName;
    private string $modelName;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        string $tableName
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->tableName = $tableName;
        $this->objectManager = $objectManager;
        $this->init();
    }

    private function init(): void
    {
        $resourceModelName = $this->dbStructureHelper->getTableModel($this->tableName);
        if (!$resourceModelName) {
            throw new Exception("Specified table '$this->tableName' cannot be managed.");
        }

        $this->modelName = $this->resolveModelNameBySubClass($resourceModelName);
    }

    private function resolveModelNameBySubClass(string $modelName): string
    {
        $modelClassName = str_replace('ResourceModel\\', '', $modelName);
        $reflection = new \ReflectionClass($modelClassName);

        if ($reflection->isSubclassOf(\M2E\TikTokShop\Model\ActiveRecord\AbstractModel::class)) {
            return $modelClassName;
        }

        return sprintf('%s\Entity', $modelName);
    }

    public function getColumns()
    {
        return $this->dbStructureHelper->getTableInfo($this->createModel()->getResource()->getMainTable());
    }

    public function createModel(): \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
    {
        return $this->objectManager->create($this->modelName);
    }

    public function createEntry(array $data): void
    {
        $helper = $this->dbStructureHelper;
        $modelInstance = $this->createModel();

        $idFieldName = $modelInstance->getIdFieldName();
        $isIdAutoIncrement = $helper->isIdColumnAutoIncrement($this->tableName);
        if ($isIdAutoIncrement) {
            unset($data[$idFieldName]);
        }

        $modelInstance->setData($data);

        $modelInstance->getResource()
                      ->save($modelInstance);
    }

    public function deleteEntries(array $ids): void
    {
        $modelInstance = $this->createModel();
        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), ['in' => $ids]);

        foreach ($collection as $item) {
            $item->getResource()->delete($item);
        }
    }

    public function updateEntries(array $ids, array $data): void
    {
        $modelInstance = $this->createModel();

        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), ['in' => $ids]);

        $idFieldName = $modelInstance->getIdFieldName();
        $isIdAutoIncrement = $this->dbStructureHelper->isIdColumnAutoIncrement($this->tableName);
        if ($isIdAutoIncrement) {
            unset($data[$idFieldName]);
        }

        if (empty($data)) {
            return;
        }

        foreach ($collection->getItems() as $item) {
            /** @var \M2E\TikTokShop\Model\ActiveRecord\AbstractModel $item */

            foreach ($data as $field => $value) {
                if ($field === $idFieldName && !$isIdAutoIncrement) {
                    $this->resourceConnection->getConnection()->update(
                        $this->dbStructureHelper->getTableNameWithPrefix($this->tableName),
                        [$idFieldName => $value],
                        "`$idFieldName` = {$item->getId()}"
                    );
                }

                $item->setData($field, $value);
            }

            $item->getResource()->save($item);
        }
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }
}
