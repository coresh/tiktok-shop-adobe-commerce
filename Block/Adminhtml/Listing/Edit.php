<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer;

class Edit extends AbstractContainer
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_listing';

        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
    }
}
