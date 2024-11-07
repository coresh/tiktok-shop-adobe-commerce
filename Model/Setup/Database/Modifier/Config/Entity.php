<?php

namespace M2E\TikTokShop\Model\Setup\Database\Modifier\Config;

use M2E\TikTokShop\Model\Setup\Database\Modifier\Config;

class Entity
{
    private $group;
    private $key;

    private Config $configModifier;

    public function __construct(
        Config $configModifier,
        $group,
        $key
    ) {
        $this->configModifier = $configModifier;
        $this->group = $group;
        $this->key = $key;
    }

    // ----------------------------------------

    public function isExists()
    {
        return $this->configModifier->isExists($this->group, $this->key);
    }

    // ---------------------------------------

    public function getGroup()
    {
        return $this->group;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        $row = $this->configModifier->getRow($this->group, $this->key);

        return isset($row['value']) ? $row['value'] : null;
    }

    // ---------------------------------------

    public function insert($value)
    {
        $result = $this->configModifier->insert($this->group, $this->key, $value);

        if ($result instanceof Config) {
            return $this;
        }

        return $result;
    }

    public function delete()
    {
        $result = $this->configModifier->delete($this->group, $this->key);

        if ($result instanceof Config) {
            return $this;
        }

        return $result;
    }

    // ---------------------------------------

    public function updateGroup($value)
    {
        $this->configModifier->updateGroup($value, $this->getWhereConditions());
        $this->group = $value;

        return $this;
    }

    public function updateKey($value)
    {
        $this->configModifier->updateKey($value, $this->getWhereConditions());
        $this->key = $value;

        return $this;
    }

    public function updateValue($value)
    {
        $this->configModifier->updateValue($value, $this->getWhereConditions());

        return $this;
    }

    //########################################

    private function getWhereConditions()
    {
        if ($this->group === null) {
            $conditions = ['`group` IS NULL'];
        } else {
            $conditions = ['`group` = ?' => $this->group];
        }

        $conditions['`key` = ?'] = $this->key;

        return $conditions;
    }
}
