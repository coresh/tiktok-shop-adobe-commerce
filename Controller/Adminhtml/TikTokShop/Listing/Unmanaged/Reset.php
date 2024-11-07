<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Unmanaged;

class Reset extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Other\Reset $listingOtherReset;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Other\Reset $listingOtherReset
    ) {
        parent::__construct();
        $this->listingOtherReset = $listingOtherReset;
    }

    public function execute()
    {
        $this->listingOtherReset->process();

        $this->messageManager->addSuccessMessage(
            __('Unmanaged Listings were reset.')
        );

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
