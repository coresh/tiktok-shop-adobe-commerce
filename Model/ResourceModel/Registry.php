<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class Registry extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_KEY = 'key';
    public const COLUMN_VALUE = 'value';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_REGISTRY,
            self::COLUMN_ID
        );
    }

    /**
     * @param string $key
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByKey(string $key): void
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            sprintf("`%s` = '%s'", self::COLUMN_KEY, $key)
        );
    }
}
