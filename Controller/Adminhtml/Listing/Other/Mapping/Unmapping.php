<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class Unmapping extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $unmanagedMappingService;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $unmanagedMappingService,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        $context = null
    ) {
        parent::__construct($context);

        $this->unmanagedMappingService = $unmanagedMappingService;
        $this->unmanagedRepository = $unmanagedRepository;
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
            $product = $this->unmanagedRepository->findById((int)$productId);

            if (!$product) {
                continue;
            }

            if (!$product->hasMagentoProductId()) {
                continue;
            }

            $this->unmanagedMappingService->unmapProduct($product);
        }

        $this->setAjaxContent('1', false);

        return $this->getResult();
    }
}
