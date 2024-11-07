<?php

namespace M2E\TikTokShop\Model\ResourceModel\Collection;

class Wrapper extends \Magento\Framework\Data\Collection\AbstractDb
{
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->getSelect()) {
            return parent::load($printQuery, $logQuery);
        }

        return $this;
    }

    public function getResource()
    {
        return null;
    }

    public function setCustomSize($size)
    {
        $this->_totalRecords = $size;
    }

    public function setCustomIsLoaded($flag)
    {
        $this->_isCollectionLoaded = $flag;
    }
}
