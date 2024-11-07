<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Moving;

class MoveToListing extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Listing\AddProductsService $addProductsService;
    private \M2E\TikTokShop\Helper\Url $urlHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Listing\AddProductsService $addProductsService,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->productRepository = $productRepository;
        $this->addProductsService = $addProductsService;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $sessionKey = \M2E\TikTokShop\Helper\View::MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY;
        $selectedProductIds = $this->sessionHelper->getValue($sessionKey);

        $sourceListing = null;
        $targetListing = $this->listingRepository->find((int)$this->getRequest()->getParam('listingId'));

        if ($targetListing === null) {
            $this->setJsonContent(
                [
                    'result' => false,
                    'message' => __('Params not valid.'),
                ]
            );

            return $this->getResult();
        }

        $errorsCount = 0;
        foreach ($selectedProductIds as $listingProductId) {
            $listingProduct = $this->productRepository->find((int)$listingProductId);

            if ($listingProduct === null) {
                continue;
            }

            $sourceListing = $listingProduct->getListing();

            if (!$this->addProductsService->addProductFromListing($listingProduct, $targetListing, $sourceListing)) {
                $errorsCount++;
            }
        }

        $this->sessionHelper->removeValue($sessionKey);

        if ($errorsCount) {
            $logViewUrl = $this->getUrl(
                '*/tiktokshop_log_listing_product/index',
                [
                    'id' => $sourceListing->getId(),
                ]
            );

            if (count($selectedProductIds) == $errorsCount) {
                $this->setJsonContent(
                    [
                        'result' => false,
                        'message' => (string)__(
                            'Products were not Moved. <a target="_blank" href="%url">View Log</a> for details.',
                            ['url' => $logViewUrl],
                        ),
                    ]
                );

                return $this->getResult();
            }

            $this->setJsonContent(
                [
                    'result' => true,
                    'isFailed' => true,
                    'message' => (string)__(
                        '%errors_count product(s) were not Moved. ' .
                        'Please <a target="_blank" href="%url">view Log</a> for the details.',
                        [
                            'errors_count' => $errorsCount,
                            'url' => $logViewUrl,
                        ],
                    ),
                ]
            );
        } else {
            $this->setJsonContent(
                [
                    'result' => true,
                    'message' => (string)__('Product(s) was Moved.'),
                ]
            );
        }

        return $this->getResult();
    }
}
