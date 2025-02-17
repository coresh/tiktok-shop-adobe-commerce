<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock;

class Transactional extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::class);
    }

    public function create(string $nick): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::COLUMN_NICK, $nick);

        return $this;
    }

    public function getNick(): string
    {
        return (string)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::COLUMN_NICK);
    }

    public function getCreateDate(): ?string
    {
        return $this->getData(\M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::COLUMN_CREATE_DATE);
    }
}
