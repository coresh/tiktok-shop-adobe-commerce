<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Registry as RegistryResource;

class Registry extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(RegistryResource::class);
    }

    public function setKey(string $key): Registry
    {
        return $this->setData(RegistryResource::COLUMN_KEY, $key);
    }

    public function setValue($value): Registry
    {
        return $this->setData(RegistryResource::COLUMN_VALUE, $value);
    }

    /**
     * @return array|mixed|null
     */
    public function getValue()
    {
        return $this->getData(RegistryResource::COLUMN_VALUE);
    }
}
