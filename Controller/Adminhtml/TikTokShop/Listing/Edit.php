<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class Edit extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::listings_items');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError($exception->getMessage());

            return $this->_redirect('*/tiktokshop_listing/index');
        }

        $this->uiListingRuntimeStorage->setListing($listing);

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Edit::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(
            __(
                'Edit %extension_title Listing "%listing_title" Settings',
                [
                    'listing_title' => $listing->getTitle(),
                    'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                ]
            ),
        );

        return $this->getResult();
    }
}
