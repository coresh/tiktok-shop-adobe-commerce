<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class Wizard extends ActiveRecord\AbstractModel
{
    public function _construct()
    {
        $this->_init(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_WIZARD, 'id');
    }
}
