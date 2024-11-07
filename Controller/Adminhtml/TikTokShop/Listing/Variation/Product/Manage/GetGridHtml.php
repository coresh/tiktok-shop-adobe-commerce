<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Variation\Product\Manage;

class GetGridHtml extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            $this->setAjaxContent('You should provide correct parameters.', false);

            return $this->getResult();
        }

        try {
            $product = $this->productRepository->get((int)$productId);
        } catch (\Throwable $exception) {
            $this->setAjaxContent($exception->getMessage(), false);

            return $this->getResult();
        }

        $view = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Variation\Product\Manage\View\Grid::class,
                '',
                ['listingProduct' => $product]
            );

        $this->setAjaxContent($view);

        return $this->getResult();
    }
}
