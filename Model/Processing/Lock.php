<?php

namespace M2E\TikTokShop\Model\Processing;

use M2E\TikTokShop\Model\ResourceModel\Processing\Lock as ProcessingLockResource;

class Lock extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ProcessingLockResource::class);
    }

    public function create(int $processingId, string $objectNick, int $objId, ?string $tag = null): self
    {
        $this->setData(ProcessingLockResource::COLUMN_PROCESSING_ID, $processingId)
             ->setData(ProcessingLockResource::COLUMN_OBJECT_NICK, $objectNick)
             ->setData(ProcessingLockResource::COLUMN_OBJECT_ID, $objId)
             ->setData(ProcessingLockResource::COLUMN_TAG, $tag);

        return $this;
    }

    public function getProcessingId(): int
    {
        return (int)$this->getData(ProcessingLockResource::COLUMN_PROCESSING_ID);
    }

    public function getNick(): string
    {
        return $this->getData(ProcessingLockResource::COLUMN_OBJECT_NICK);
    }

    public function getObjectId(): int
    {
        return (int)$this->getData(ProcessingLockResource::COLUMN_OBJECT_ID);
    }

    public function getTag(): ?string
    {
        return $this->getData(ProcessingLockResource::COLUMN_TAG);
    }
}
