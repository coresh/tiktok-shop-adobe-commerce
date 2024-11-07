<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Listing\Switcher
 */
abstract class Switcher extends AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setAddListingUrl('');

        $this->setTemplate('M2E_TikTokShop::listing/switcher.phtml');
    }

    //########################################
}
