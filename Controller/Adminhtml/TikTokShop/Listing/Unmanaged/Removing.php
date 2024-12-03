<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Unmanaged;

class Removing extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $deleteService;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $deleteService
    ) {
        parent::__construct();
        $this->listingOtherRepository = $listingOtherRepository;
        $this->deleteService = $deleteService;
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
            $listingOther = $this->listingOtherRepository->get((int)$productId);

            $this->deleteService->process($listingOther);
        }

        $this->setAjaxContent('1', false);

        return $this->getResult();
    }
}
