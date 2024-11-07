<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

use M2E\TikTokShop\Helper\Data\Session;

class Review extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private Session $sessionHelper;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        Session $sessionHelper
    ) {
        parent::__construct();

        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \JsonException
     */
    public function execute()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listing = $this->listingRepository->get($listingId);
        $this->uiListingRuntimeStorage->setListing($listing);

        $ids = $this->sessionHelper->getValue('added_products_ids');

        if (
            empty($ids)
            && !$this->getRequest()->getParam('disable_list')
        ) {
            return $this->_redirect('*/*/view', ['id' => $listingId]);
        }
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product\Review $blockReview */
        $blockReview = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product\Review::class,
            '',
            [
                'listing' => $listing,
                'data' => [
                    'products_count' => count($ids),
                ],
            ]
        );

        $additionalData = $listing->getAdditionalData();

        if (isset($additionalData['source']) && $source = $additionalData['source']) {
            $blockReview->setSource($source);
        }

        unset($additionalData['source']);
        $listing->setAdditionalData($additionalData);
        $this->listingRepository->save($listing);

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Congratulations'));

        $this->addContent($blockReview);

        return $this->getResult();
    }
}
