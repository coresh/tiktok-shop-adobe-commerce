<?php

namespace M2E\TikTokShop\Model\Processing;

use M2E\TikTokShop\Model\ResourceModel\Processing\PartialData as PartialDataResource;

class PartialData extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(PartialDataResource::class);
    }

    public function create(
        \M2E\TikTokShop\Model\Processing $processing,
        array $data,
        int $partNumber
    ): self {
        $this->setData(PartialDataResource::COLUMN_PROCESSING_ID, $processing->getId())
             ->setData(PartialDataResource::COLUMN_PART_NUMBER, $partNumber)
             ->setData(PartialDataResource::COLUMN_DATA, json_encode($data, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function getProcessingId(): int
    {
        return (int)$this->getData(PartialDataResource::COLUMN_PROCESSING_ID);
    }

    public function getPartNumber(): int
    {
        return (int)$this->getData(PartialDataResource::COLUMN_PART_NUMBER);
    }

    public function getResultData(): array
    {
        $data = $this->getData(PartialDataResource::COLUMN_DATA);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}
