<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Inspection;

class Cron extends AbstractInspection
{
    public string $cronLastRunTime = 'N/A';
    public bool $cronIsNotWorking = false;
    public string $cronCurrentRunner = '';
    public string $cronServiceAuthKey = '';
    public bool $isMagentoCronDisabled = false;
    public bool $isControllerCronDisabled = false;
    public bool $isPubCronDisabled = false;

    private \M2E\TikTokShop\Helper\Module\Cron $cronHelper;
    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Cron $cronHelper,
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->cronHelper = $cronHelper;
        $this->config = $config;

        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInspectionCron');
        $this->setTemplate('control_panel/inspection/cron.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->cronCurrentRunner = ucwords(str_replace('_', ' ', $this->cronHelper->getRunner()));
        $this->cronServiceAuthKey = (string)$this->config->getGroupValue('/cron/service/', 'auth_key');

        $cronLastRunTime = $this->cronHelper->getLastRun();
        if ($cronLastRunTime !== null) {
            $this->cronLastRunTime = $cronLastRunTime->format('Y-m-d H:i:s');
            $this->cronIsNotWorking = $this->cronHelper->isLastRunMoreThan(1, true);
        }

        $this->isMagentoCronDisabled = (bool)(int)$this->config->getGroupValue('/cron/magento/', 'disabled');
        $this->isControllerCronDisabled = (bool)(int)$this->config->getGroupValue(
            '/cron/service_controller/',
            'disabled'
        );
        $this->isPubCronDisabled = (bool)(int)$this->config->getGroupValue('/cron/service_pub/', 'disabled');

        return parent::_beforeToHtml();
    }
}
