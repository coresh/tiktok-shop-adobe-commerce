<?php

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Config as ConfigResource;

class Config extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    protected function _construct()
    {
        $this->_init(ConfigResource::class);
    }

    public function setGroup(string $group): self
    {
        $this->setData(ConfigResource::COLUMN_GROUP, $group);

        return $this;
    }

    public function getGroup(): string
    {
        return (string)$this->getData(ConfigResource::COLUMN_GROUP);
    }

    public function setKey(string $key): self
    {
        $this->setData(ConfigResource::COLUMN_KEY, $key);

        return $this;
    }

    public function getKey(): string
    {
        return (string)$this->getData(ConfigResource::COLUMN_KEY);
    }

    public function setValue($value): self
    {
        $this->setData(ConfigResource::COLUMN_VALUE, $value);

        return $this;
    }

    public function getValue()
    {
        return $this->getData(ConfigResource::COLUMN_VALUE);
    }
}
