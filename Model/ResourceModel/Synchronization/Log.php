<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Synchronization;

class Log extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_OPERATION_HISTORY_ID = 'operation_history_id';
    public const COLUMN_TASK = 'task';
    public const COLUMN_INITIATOR = 'initiator';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_DETAILED_DESCRIPTION = 'detailed_description';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SYNCHRONIZATION_LOG, 'id');
    }
}
