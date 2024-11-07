<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

use M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class Settings extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
