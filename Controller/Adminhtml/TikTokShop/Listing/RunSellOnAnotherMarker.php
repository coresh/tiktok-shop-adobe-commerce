<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class RunSellOnAnotherMarker extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\AbstractAction
{
    private \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService;

    public function __construct(
        \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        parent::__construct($productRepository);

        $this->actionService = $actionService;
    }

    public function execute()
    {
        $fromListingId = $this->getRequest()->getParam('from_listing_id');
        $toListingId = $this->getRequest()->getParam('to_listing_id');

        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->setRawContent('You should select Products');
        }

        $products = $this->oldGridLoadProducts($listingsProductsIds);

        $this->setJsonContent(['result' => 'success']);

        return $this->getResult();
    }
}
