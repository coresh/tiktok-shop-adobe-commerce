<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class ListingTutorial extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
