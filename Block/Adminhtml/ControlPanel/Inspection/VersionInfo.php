<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Inspection;

class VersionInfo extends AbstractInspection
{
    public string $latestPublicVersion = '';

    private \M2E\TikTokShop\Model\Module $module;

    public function __construct(
        \M2E\TikTokShop\Model\Module $module,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->module = $module;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInspectionVersionInfo');
        $this->setTemplate('control_panel/inspection/versionInfo.phtml');

        $this->prepareInfo();
    }

    // ----------------------------------------

    protected function prepareInfo()
    {
        if ($this->module->hasLatestVersion()) {
            $this->latestPublicVersion = $this->module->getLatestVersion();
        }
    }
}
