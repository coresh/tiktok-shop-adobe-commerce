<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class Installation extends \M2E\TikTokShop\Controller\Adminhtml\Wizard\AbstractInstallation
{
    public function execute()
    {
        return $this->installationAction();
    }
}
