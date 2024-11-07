<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing;

use M2E\TikTokShop\Controller\Adminhtml\AbstractListing;

class Edit extends AbstractListing
{
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listing = $this->listingRepository->get($params['id']);

        if ($this->getRequest()->isPost()) {
            $listing->addData($params);
            $this->listingRepository->save($listing);

            return $this->getResult();
        }

        $this->globalData->setValue('edit_listing', $listing);

        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Listing\Edit::class)
        );

        return $this->getResult();
    }
}
