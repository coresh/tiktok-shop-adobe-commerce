<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\SellOnAnotherMarket;

class SelectListing extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
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
        $sourceListingId = (int)$this->getRequest()->getParam('listing_id');
        $sourceListing = $this->listingRepository->get($sourceListingId);

        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\SellOnAnotherListing\Grid::class,
            '',
            [
                'sourceListing' => $sourceListing,
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
