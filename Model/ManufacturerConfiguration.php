<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration as ManufacturerConfigurationResource;

class ManufacturerConfiguration extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration::class);
    }

    public function init(
        string $title,
        int $accountId,
        string $manufacturerId,
        array $responsiblePersonIds
    ): self {
        $this->setTitle($title);
        $this->setAccountId($accountId);
        $this->setManufacturerId($manufacturerId);
        $this->setResponsiblePersonIds($responsiblePersonIds);

        return $this;
    }

    public function setTitle(string $title): self
    {
        $title = trim($title);
        if (empty($title)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('The title must not be empty');
        }

        $this->setData(ManufacturerConfigurationResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ManufacturerConfigurationResource::COLUMN_TITLE);
    }

    public function setAccountId(int $accountId): self
    {
        $this->setData(ManufacturerConfigurationResource::COLUMN_ACCOUNT_ID, $accountId);

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ManufacturerConfigurationResource::COLUMN_ACCOUNT_ID);
    }

    public function setManufacturerId(string $manufacturerId): self
    {
        $this->setData(ManufacturerConfigurationResource::COLUMN_MANUFACTURER_ID, $manufacturerId);

        return $this;
    }

    public function getManufacturerId(): string
    {
        return (string)$this->getData(ManufacturerConfigurationResource::COLUMN_MANUFACTURER_ID);
    }

    public function setResponsiblePersonIds(array $responsiblePersonIds): self
    {
        $responsiblePersonIds = array_unique($responsiblePersonIds);

        $this->setData(
            ManufacturerConfigurationResource::COLUMN_RESPONSIBLE_PERSON_IDS,
            json_encode($responsiblePersonIds, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getResponsiblePersonIds(): array
    {
        $data = $this->getData(ManufacturerConfigurationResource::COLUMN_RESPONSIBLE_PERSON_IDS);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }
}
