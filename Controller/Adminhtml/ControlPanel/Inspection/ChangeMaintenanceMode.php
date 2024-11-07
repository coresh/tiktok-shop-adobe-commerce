<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;

class ChangeMaintenanceMode extends AbstractMain
{
    private \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper;
    private \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper,
        \M2E\TikTokShop\Model\Module $module
    ) {
        parent::__construct($module);
        $this->controlPanelHelper = $controlPanelHelper;
        $this->maintenanceHelper = $maintenanceHelper;
    }

    public function execute()
    {
        if ($this->maintenanceHelper->isEnabled()) {
            $this->maintenanceHelper->disable();
        } else {
            $this->maintenanceHelper->enable();
        }

        $this->messageManager->addSuccess(__('Changed.'));

        return $this->_redirect($this->controlPanelHelper->getPageUrl());
    }
}
