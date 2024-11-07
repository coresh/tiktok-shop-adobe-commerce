<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;

class SetMagentoCoreSetupValue extends AbstractMain
{
    private \Magento\Framework\Module\ModuleResource $moduleResource;
    private \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $dbContext,
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\TikTokShop\Model\Module $module
    ) {
        parent::__construct($module);
        $this->moduleResource = new \Magento\Framework\Module\ModuleResource($dbContext);
        $this->controlPanelHelper = $controlPanelHelper;
    }

    public function execute()
    {
        $version = $this->getRequest()->getParam('version');
        if (!$version) {
            $this->messageManager->addWarning('Version is not provided.');

            return $this->_redirect($this->controlPanelHelper->getPageUrl());
        }

        $version = str_replace(',', '.', $version);
        if (!version_compare(\M2E\TikTokShop\Model\Setup\Upgrader::MIN_SUPPORTED_VERSION_FOR_UPGRADE, $version, '<=')) {
            $this->messageManager->addError(
                sprintf(
                    'Extension upgrade can work only from %s version.',
                    \M2E\TikTokShop\Model\Setup\Upgrader::MIN_SUPPORTED_VERSION_FOR_UPGRADE
                )
            );

            return $this->_redirect($this->controlPanelHelper->getPageUrl());
        }

        $this->moduleResource->setDbVersion(\M2E\TikTokShop\Helper\Module::IDENTIFIER, $version);
        $this->moduleResource->setDataVersion(\M2E\TikTokShop\Helper\Module::IDENTIFIER, $version);

        $this->messageManager->addSuccess(__('Extension upgrade was completed.'));

        return $this->_redirect($this->controlPanelHelper->getPageUrl());
    }
}
