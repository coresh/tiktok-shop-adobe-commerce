<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Moving;

class PrepareMoveToListing extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->sessionHelper = $sessionHelper;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $selectedProductsIds = (array)$this->getRequest()->getParam('other_product_ids');

        $sessionKey = \M2E\TikTokShop\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $this->sessionHelper->setValue($sessionKey, $selectedProductsIds);

        $row = $this->unmanagedRepository->findShopIdByUnmanagedIdsAndAccount($selectedProductsIds, $accountId);

        if ($row !== false) {
            $response = [
                'result' => true,
                'shopId' => (int)$row['shop_id']
            ];
        } else {
            $response = [
                'result' => false,
                'message' => __('Magento product not found. Please reload the page.'),
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}
