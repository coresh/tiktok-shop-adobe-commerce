<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Debug
 */
class Debug extends AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelDebug');
        $this->setTemplate('control_panel/tabs/debug.phtml');
    }

    //########################################
}
