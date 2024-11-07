<?php

namespace M2E\TikTokShop\Model\Lock;

/**
 * Class \M2E\TikTokShop\Model\Lock\Transactional
 */
class Transactional extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::class);
    }

    public function getNick()
    {
        return $this->getData('nick');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }
}
