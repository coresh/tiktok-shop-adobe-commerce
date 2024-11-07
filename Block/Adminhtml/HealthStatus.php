<?php

namespace M2E\TikTokShop\Block\Adminhtml;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\HealthStatus
 */
class HealthStatus extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    //########################################

    protected function _construct()
    {
        parent::_construct();

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        return parent::_toHtml() . '<div id="healthStatus_tab_container"></div>';
    }

    //########################################
}
