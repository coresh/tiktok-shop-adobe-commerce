<?php

namespace M2E\TikTokShop\Model\Lock;

class Item extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Lock\Item::class);
    }

    public function setNick(string $nick): self
    {
        $this->setData('nick', $nick);

        return $this;
    }

    public function getNick()
    {
        return $this->getData('nick');
    }

    public function setParentId($id): self
    {
        $this->setData('parent_id', $id);

        return $this;
    }

    public function getParentId()
    {
        return $this->getData('parent_id');
    }

    public function getContentData()
    {
        return $this->getData('data');
    }

    //----------------------------------------

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }
}
