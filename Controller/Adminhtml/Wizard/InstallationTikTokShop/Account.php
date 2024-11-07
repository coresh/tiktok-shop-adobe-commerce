<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class Account extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
