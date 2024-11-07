<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop;

abstract class AbstractListing extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::listings_items');
    }
}
