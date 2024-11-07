<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing;

use M2E\TikTokShop\Block\Adminhtml\Listing\Switcher as AbstractSwitcher;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Switcher
 */
class Switcher extends AbstractSwitcher
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block

        $this->setAddListingUrl('*/ebay_listing_create/index');
    }

    //########################################
}
