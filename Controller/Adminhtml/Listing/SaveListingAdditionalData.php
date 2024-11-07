<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing;

class SaveListingAdditionalData extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $listingId = $this->getRequest()->getParam('id');
        $paramName = $this->getRequest()->getParam('param_name');
        $paramValue = $this->getRequest()->getParam('param_value');

        if (empty($listingId) || empty($paramName) || empty($paramValue)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listing = $this->listingRepository->get($listingId);

        $listing->setSetting('additional_data', $paramName, $paramValue);
        $this->listingRepository->save($listing);

        $this->setAjaxContent(0, false);

        return $this->getResult();
    }
}
