<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Product\View;

class GetListingQualityRecommendation extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Product\ListingQuality\Recommendations\HtmlRender $recommendationsHtmlRender;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Product\ListingQuality\Recommendations\HtmlRender $recommendationsHtmlRender
    ) {
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->recommendationsHtmlRender = $recommendationsHtmlRender;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        if (empty($productId)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Missing required parameter product_id');
        }

        $product = $this->productRepository->get((int)$productId);

        $this->setRawContent(
            $this->recommendationsHtmlRender->render(
                $product->getListingQuality()->getRecommendationCollection()
            )
        );

        return $this->getResult();
    }
}
