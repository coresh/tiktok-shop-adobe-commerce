<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log\Listing\Product;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log\AbstractListing
{
    private \Magento\Framework\Filter\FilterManager $filterManager;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct();

        $this->filterManager = $filterManager;
        $this->listingRepository = $listingRepository;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function execute()
    {
        $listingId = $this->getRequest()->getParam(
            \M2E\TikTokShop\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
            false
        );
        $listingProductId = $this->getRequest()->getParam(
            \M2E\TikTokShop\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            false
        );

        if ($listingId) {
            $listing = $this->listingRepository->find($listingId);

            if ($listing === null) {
                $this->getMessageManager()->addErrorMessage(__('Listing does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                __(
                    '%extension_title Listing "%s" Log',
                    [
                        's' => $listing->getTitle(),
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                    ]
                ),
            );
        } elseif ($listingProductId) {
            $listingProduct = $this->listingProductRepository->find($listingProductId);

            if ($listingProduct === null) {
                $this->getMessageManager()->addErrorMessage(__('Listing Product does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                __(
                    '%extension_title Listing Product "%name" Log',
                    [
                        'name' => $this->filterManager->truncate($listingProduct->getMagentoProduct()->getName(), ['length' => 28]),
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                    ]
                )
            );
        } else {
            $this->getResult()->getConfig()->getTitle()->prepend(__('Listings Logs & Events'));
        }

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Log\Listing\Product\View::class)
        );

        return $this->getResult();
    }
}
