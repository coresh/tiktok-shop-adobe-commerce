<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Synchronization;

use M2E\TikTokShop\Model\ResourceModel\Synchronization\Log as SyncLogResource;

class Log extends \M2E\TikTokShop\Model\Log\AbstractModel
{
    public const TYPE_FATAL_ERROR = 100;

    public const TASK_ALL = 100;
    public const _TASK_ALL = 'All';

    public const TASK_OTHER = 0;
    public const _TASK_OTHER = 'Other';

    public const TASK_LISTINGS = 2;
    public const _TASK_LISTINGS = 'M2E TikTok Shop Connect Listings';

    public const TASK_OTHER_LISTINGS = 5;
    public const _TASK_OTHER_LISTINGS = 'Unmanaged Listings';

    public const TASK_ORDERS = 3;
    public const _TASK_ORDERS = 'Orders';

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Synchronization\Log::class);
    }

    public function create(
        int $initiator,
        int $task,
        ?int $operationHistoryId,
        string $description,
        int $type,
        ?string $detailedDescription = null
    ): self {
        \M2E\Core\Helper\Data::validateInitiator($initiator);

        $this
            ->setData(SyncLogResource::COLUMN_INITIATOR, $initiator)
            ->setData(SyncLogResource::COLUMN_TASK, $task)
            ->setData(SyncLogResource::COLUMN_OPERATION_HISTORY_ID, $operationHistoryId)
            ->setData(SyncLogResource::COLUMN_DESCRIPTION, $description)
            ->setData(SyncLogResource::COLUMN_TYPE, $type)
            ->setData(SyncLogResource::COLUMN_DETAILED_DESCRIPTION, $detailedDescription);

        return $this;
    }
}
