<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class Save extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Helper\Url $urlHelper;
    private \M2E\TikTokShop\Model\Listing\UpdateService $listingUpdateService;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\UpdateService $listingUpdateService,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Helper\Url $urlHelper
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->urlHelper = $urlHelper;
        $this->listingUpdateService = $listingUpdateService;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::listings_items');
    }

    public function execute()
    {
        if (!$post = $this->getRequest()->getParams()) {
            $this->_redirect('*/tiktokshop_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));

            return $this->_redirect('*/tiktokshop_listing/index');
        }

        try {
            $this->listingUpdateService->update($listing, $post);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));

            return $this->_redirect('*/tiktokshop_listing/index');
        }

        $this->getMessageManager()->addSuccess(__('The Listing was saved.'));

        $redirectUrl = $this->urlHelper
            ->getBackUrl(
                'list',
                [],
                ['edit' => ['id' => $id]]
            );

        return $this->_redirect($redirectUrl);
    }
}
