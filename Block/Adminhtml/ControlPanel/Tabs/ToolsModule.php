<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\ToolsModule
 */
class ToolsModule extends AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelToolsModule');
        // ---------------------------------------

        $this->setTemplate('control_panel/tabs/tools_module.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'tabs',
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\ToolsModule\Tabs::class)
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
