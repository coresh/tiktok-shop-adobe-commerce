<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

use M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class ListingAccount extends Installation
{
    public function execute()
    {
        return $this->_redirect('*/tiktokshop_listing_create', ['step' => 1, 'wizard' => true, 'clear' => true]);
    }
}
