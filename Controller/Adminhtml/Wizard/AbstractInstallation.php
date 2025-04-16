<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractWizard;

abstract class AbstractInstallation extends AbstractWizard
{
    protected function getNick(): string
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::WIZARD_INSTALLATION_NICK;
    }

    protected function init(): void
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Configuration of %channel Integration', ['channel' => (string)__(\M2E\TikTokShop\Helper\Module::getChannelTitle())]));
    }
}
