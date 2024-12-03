<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class AutoMap extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        $context = null
    ) {
        parent::__construct($context);

        $this->unmanagedRepository = $unmanagedRepository;
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
            $unmanaged = $this->unmanagedRepository->get((int)$productId);
            if ($unmanaged->hasMagentoProductId()) {
                continue;
            }

            $productsForMapping[] = $unmanaged;
        }

        if (!$this->mappingService->autoMapUnmanagedProducts($productsForMapping)) {
            $this->setAjaxContent('1', false);

            return $this->getResult();
        }

        return $this->getResult();
    }
}
