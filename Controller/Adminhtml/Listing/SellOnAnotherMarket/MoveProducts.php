<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\SellOnAnotherMarket;

class MoveProducts extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\GlobalProduct\Move $move;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\GlobalProduct\Move $move,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingRepository = $listingRepository;
        $this->move = $move;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $sourceListing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('source_listing_id'));
        $targetListing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('target_listing_id'));

        $products = $this->getProductsFromRequest();

        $failResults = [];
        foreach ($products as $product) {
            $moveResult = $this->move->execute($sourceListing, $targetListing, $product);
            if ($moveResult->isFail()) {
                $failResults[] = [
                    'product_id' => $product->getId(),
                    'fail_messages' => $moveResult->getFailMessages(),
                ];
            }
        }

        $resultStatus = 'success';
        if (count($failResults) !== 0) {
            $resultStatus = count($failResults) === count($products)
                ? 'error'
                : 'warning';
        }

        $this->setJsonContent([
            'result' => $resultStatus,
            'fails' => $failResults
        ]);

        return $this->getResult();
    }

    private function getProductsFromRequest(): array
    {
        $selectedProductIds = explode(',', $this->getRequest()->getParam('selected_products'));

        return $this->productRepository->getByIds($selectedProductIds);
    }
}
