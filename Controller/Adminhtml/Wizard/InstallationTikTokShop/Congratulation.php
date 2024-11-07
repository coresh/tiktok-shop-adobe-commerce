<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

use M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class Congratulation extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->congratulationAction();
    }
}
