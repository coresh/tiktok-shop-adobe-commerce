<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Moving;

class PrepareMoveToListing extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->sessionHelper = $sessionHelper;
    }

    public function execute()
    {
        $sessionKey = \M2E\TikTokShop\Helper\View::MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY;

        if ((bool)$this->getRequest()->getParam('is_first_part')) {
            $this->sessionHelper->removeValue($sessionKey);
        }

        $selectedProductIds = [];
        if ($sessionValue = $this->sessionHelper->getValue($sessionKey)) {
            $selectedProductIds = $sessionValue;
        }

        $selectedProductsPart = $this->getRequest()->getParam('products_part');
        $selectedProductsPart = explode(',', $selectedProductsPart);

        $selectedProductIds = array_merge($selectedProductIds, $selectedProductsPart);
        $this->sessionHelper->setValue($sessionKey, $selectedProductIds);

        if (!(bool)$this->getRequest()->getParam('is_last_part')) {
            $this->setJsonContent(['result' => true]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'result' => true,
        ]);

        return $this->getResult();
    }
}
