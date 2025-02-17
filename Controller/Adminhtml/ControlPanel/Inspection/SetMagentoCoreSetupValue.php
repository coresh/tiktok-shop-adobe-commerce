<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;

class SetMagentoCoreSetupValue extends AbstractMain
{
    private \Magento\Framework\Module\ModuleResource $moduleResource;
    private \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper;
    private \M2E\TikTokShop\Setup\UpgradeCollection $upgradeCollection;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $dbContext,
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\TikTokShop\Setup\UpgradeCollection $upgradeCollection
    ) {
        parent::__construct();
        $this->moduleResource = new \Magento\Framework\Module\ModuleResource($dbContext);
        $this->controlPanelHelper = $controlPanelHelper;
        $this->upgradeCollection = $upgradeCollection;
    }

    public function execute()
    {
        $version = $this->getRequest()->getParam('version');
        if (!$version) {
            $this->messageManager->addWarning('Version is not provided.');

            return $this->_redirect($this->controlPanelHelper->getPageUrl());
        }

        $version = str_replace(',', '.', $version);
        if (!version_compare($this->upgradeCollection->getMinAllowedVersion(), $version, '<=')) {
            $this->messageManager->addError(
                sprintf(
                    'Extension upgrade can work only from %s version.',
                    $this->upgradeCollection->getMinAllowedVersion()
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
