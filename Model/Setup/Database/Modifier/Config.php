<?php

namespace M2E\TikTokShop\Model\Setup\Database\Modifier;

use M2E\TikTokShop\Model\Setup\Database\Modifier\Config\Entity;

class Config extends AbstractModifier
{
    private Config\EntityFactory $entityFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Setup\Database\Modifier\Config\EntityFactory $entityFactory,
        \Magento\Framework\Setup\SetupInterface $installer,
        string $tableName
    ) {
        parent::__construct($installer, $tableName);
        $this->entityFactory = $entityFactory;
    }

    /**
     * @param string $group
     * @param string $key
     *
     * @return mixed
     */
    public function getRow($group, $key)
    {
        $group = $this->prepareGroup($group);
        $key = $this->prepareKey($key);

        $query = $this->connection
            ->select()
            ->from($this->tableName)
            ->where('`key` = ?', $key);

        if ($group === null) {
            $query->where('`group` IS NULL');
        } else {
            $query->where('`group` = ?', $group);
        }

        return $this->connection->fetchRow($query);
    }

    /**
     * @param string $group
     * @param string $key
     *
     * @return Entity
     */
    public function getEntity($group, $key): Entity
    {
        return $this->entityFactory->create($this, $group, $key);
    }

    // ----------------------------------------

    /**
     * @param string $group
     * @param string|null $key
     *
     * @return bool
     */
    public function isExists($group, $key = null)
    {
        $group = $this->prepareGroup($group);
        $key = $this->prepareKey($key);

        $query = $this->connection
            ->select()
            ->from($this->tableName);

        if ($group === null) {
            $query->where('`group` IS NULL');
        } else {
            $query->where('`group` = ?', $group);
        }

        if ($key !== null) {
            $query->where('`key` = ?', $key);
        }

        return !empty($this->connection->fetchOne($query));
    }

    // ---------------------------------------

    /**
     * @param string $group
     * @param string $key
     * @param string|null $value
     * @param NULL $notice is not supported. left for backward compatibility
     *
     * @return $this|int
     */
    public function insert($group, $key, $value = null, $notice = null)
    {
        if ($this->isExists($group, $key)) {
            return $this;
        }

        $preparedData = [
            'group' => $this->prepareGroup($group),
            'key' => $this->prepareKey($key),
        ];

        $value !== null && $preparedData['value'] = $value;

        $preparedData['update_date'] = $this->getCurrentDateTime();
        $preparedData['create_date'] = $this->getCurrentDateTime();

        return $this->connection->insert($this->tableName, $preparedData);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string|array $where
     *
     * @return int
     */
    public function update($field, $value, $where)
    {
        $field == 'group' && $value = $this->prepareGroup($value);
        $field == 'key' && $value = $this->prepareKey($value);

        $preparedData = [
            $field => $value,
            'update_date' => $this->getCurrentDateTime(),
        ];

        return $this->connection->update($this->tableName, $preparedData, $where);
    }

    /**
     * @param string $group
     * @param string|null $key
     *
     * @return $this|int
     */
    public function delete($group, $key = null)
    {
        if (!$this->isExists($group, $key)) {
            return $this;
        }

        $group = $this->prepareGroup($group);
        $key = $this->prepareKey($key);

        if ($group === null) {
            $where = ['`group` IS NULL'];
        } else {
            $where = ['`group` = ?' => $group];
        }

        if ($key !== null) {
            $where['`key` = ?'] = $key;
        }

        return $this->connection->delete($this->tableName, $where);
    }

    //########################################

    /**
     * @param string $value
     * @param string|array $where
     *
     * @return int
     */
    public function updateGroup($value, $where)
    {
        return $this->update('group', $value, $where);
    }

    /**
     * @param string $value
     * @param string|array $where
     *
     * @return int
     */
    public function updateKey($value, $where)
    {
        return $this->update('key', $value, $where);
    }

    /**
     * @param string $value
     * @param string|array $where
     *
     * @return int
     */
    public function updateValue($value, $where)
    {
        return $this->update('value', $value, $where);
    }

    //########################################

    private function prepareGroup($group)
    {
        if ($group === null || $group === '/') {
            return $group;
        }

        return '/' . trim($group, '/') . '/';
    }

    private function prepareKey($key)
    {
        if ($key === null) {
            return $key;
        }

        return strtolower($key);
    }

    //########################################

    private function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s', (int)gmdate('U'));
    }

    //########################################
}
