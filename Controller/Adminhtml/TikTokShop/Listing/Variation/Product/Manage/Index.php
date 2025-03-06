<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Variation\Product\Manage;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingProductRepository = $listingProductRepository;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            $this->setAjaxContent('You should provide correct parameters.', false);

            return $this->getResult();
        }

        try {
            $listingProduct = $this->listingProductRepository->get((int)$productId);
        } catch (\M2E\Core\Model\Exception $exception) {
            $this->setAjaxContent($exception->getMessage());

            return $this->getResult();
        }

        $filterByIds = [];
        if (($idsString = $this->getRequest()->getParam('filter_by_ids')) !== null) {
            $filterByIds = explode(',', $idsString);
        }

        $view = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Variation\Product\Manage\View::class,
                '',
                [
                    'listingProduct' => $listingProduct,
                    'filterByIds' => $filterByIds
                ]
            );

        $this->setAjaxContent($view);

        return $this->getResult();
    }
}
