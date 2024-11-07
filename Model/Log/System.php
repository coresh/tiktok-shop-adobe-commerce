<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Log;

use M2E\TikTokShop\Model\ResourceModel\Log\System as LogSystemResource;

class System extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const TYPE_LOGGER = 100;
    public const TYPE_EXCEPTION = 200;
    public const TYPE_EXCEPTION_CONNECTOR = 201;
    public const TYPE_FATAL_ERROR = 300;

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(LogSystemResource::class);
    }

    public function init(int $type, string $class, string $message, string $details, array $additionalData): self
    {
        $this
            ->setData(LogSystemResource::COLUMN_TYPE, $type)
            ->setData(LogSystemResource::COLUMN_CLASS, $class)
            ->setData(LogSystemResource::COLUMN_DESCRIPTION, $message)
            ->setData(LogSystemResource::COLUMN_DETAILED_DESCRIPTION, $details)
            ->setData(LogSystemResource::COLUMN_ADDITIONAL_DATA, print_r($additionalData, true));

        return $this;
    }
}
