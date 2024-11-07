<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel;

class Setup extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_VERSION_FROM = 'version_from';
    public const COLUMN_VERSION_TO = 'version_to';
    public const COLUMN_IS_COMPLETED = 'is_completed';
    public const COLUMN_PROFILER_DATA = 'profiler_data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public const LONG_COLUMN_SIZE = 16777217;

    public function _construct(): void
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SETUP, 'id');
    }
}
