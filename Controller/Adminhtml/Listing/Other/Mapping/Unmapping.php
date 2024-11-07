<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class Unmapping extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingOtherRepository = $listingOtherRepository;
    }

    public function execute()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (!$productIds) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        $productArray = explode(',', $productIds);

        if (empty($productArray)) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        foreach ($productArray as $productId) {
            $listingOther = $this->listingOtherRepository->get($productId);

            if (!$listingOther->hasMagentoProductId()) {
                continue;
            }

            $listingOther->unmapProduct();

            $this->listingOtherRepository->save($listingOther);
        }

        $this->setAjaxContent('1', false);

        return $this->getResult();
    }
}
