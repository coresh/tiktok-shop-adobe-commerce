<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock;

class Item extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::class);
    }

    public function create(string $nick, ?int $parentId): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_NICK, $nick);
        if ($parentId !== null) {
            $this->setData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_PARENT_ID, $parentId);
        }

        return $this;
    }

    public function getNick(): string
    {
        return (string)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_NICK);
    }

    public function getParentId(): ?int
    {
        $value = $this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_PARENT_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    public function setContentData(array $data): void
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_DATA, json_encode($data));
    }

    public function getContentData(): array
    {
        $value = $this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    //----------------------------------------

    public function actualize(): void
    {
        $this->setData(
            \M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_UPDATE_DATE,
            \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\TikTokShop\Helper\Date::createDateGmt(
            $this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_UPDATE_DATE)
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\TikTokShop\Helper\Date::createDateGmt(
            $this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::COLUMN_CREATE_DATE)
        );
    }
}
