<?php

namespace M2E\TikTokShop\Block\Adminhtml\Wizard\InstallationTikTokShop\Installation;

use M2E\TikTokShop\Block\Adminhtml\Wizard\InstallationTikTokShop\Installation;

class ListingTutorial extends Installation
{
    public const INSTALLATION_SKIP = 'skip';
    public const INSTALLATION_COMPLETE = 'complete';

    protected function _construct(): void
    {
        parent::_construct();

        $this->updateButton('continue', 'label', __('Create First Listing'));
        $this->updateButton('continue', 'class', 'primary');

        $completeUrl = $this->getUrl('*/*/complete', [
            'status' => self::INSTALLATION_COMPLETE,
        ]);
        $this->updateButton('continue', 'onclick', 'setLocation(\'' . $completeUrl . '\')');

        // ---------------------------------------

        $skipUrl = $this->getUrl('*/*/complete', [
            'status' => self::INSTALLATION_SKIP,
        ]);

        $this->addButton('skip', [
            'label' => __('Skip'),
            'class' => 'primary',
            'id' => 'skip',
            'onclick' => 'setLocation(\'' . $skipUrl . '\')',
        ]);
    }

    protected function getStep(): string
    {
        return 'listingTutorial';
    }
}
