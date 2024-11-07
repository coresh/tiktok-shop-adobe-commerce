<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class AutoMap extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\TikTokShop\Model\Listing\Other\MappingService $mappingService;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Other\MappingService $mappingService,
        \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingOtherRepository = $listingOtherRepository;
        $this->mappingService = $mappingService;
    }

    public function execute()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            $this->setAjaxContent('You should select one or more Products', false);

            return $this->getResult();
        }

        $productIds = explode(',', $productIds);

        $productsForMapping = [];
        foreach ($productIds as $productId) {
            $listingOther = $this->listingOtherRepository->get($productId);
            if ($listingOther->hasMagentoProductId()) {
                continue;
            }

            $productsForMapping[] = $listingOther;
        }

        if (!$this->mappingService->autoMapOtherListingsProducts($productsForMapping)) {
            $this->setAjaxContent('1', false);

            return $this->getResult();
        }

        return $this->getResult();
    }
}
