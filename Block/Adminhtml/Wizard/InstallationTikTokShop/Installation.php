<?php

namespace M2E\TikTokShop\Block\Adminhtml\Wizard\InstallationTikTokShop;

abstract class Installation extends \M2E\TikTokShop\Block\Adminhtml\Wizard\Installation
{
    protected function _construct()
    {
        parent::_construct();

        $this->updateButton('continue', 'onclick', 'InstallationWizardObj.continueStep();');
    }

    protected function _toHtml()
    {
        $this->js->add(
            <<<JS
    require([
        'TikTokShop/Wizard/InstallationTikTokShop',
    ], function(){
        window.InstallationWizardObj = new WizardInstallationTikTokShop();
    });
JS
        );

        return parent::_toHtml();
    }
}
