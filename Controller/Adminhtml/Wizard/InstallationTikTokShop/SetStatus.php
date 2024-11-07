<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

use M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class SetStatus extends Installation
{
    public function execute()
    {
        return $this->setStatusAction();
    }
}
