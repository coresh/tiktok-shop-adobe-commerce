<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class Map extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $unmanagedMappingService;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $unmanagedMappingService,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->productCollectionFactory = $productCollectionFactory;
        $this->unmanagedMappingService = $unmanagedMappingService;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id'); // Magento
        $productUnmanagedId = $this->getRequest()->getParam('other_product_id');

        if (!$productId || !$productUnmanagedId) {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);

        $magentoCatalogProductModel = $collection->getFirstItem();
        if ($magentoCatalogProductModel->isEmpty()) {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        $productId = $magentoCatalogProductModel->getId();

        if (!$this->unmanagedMappingService->manualMapProduct((int)$productUnmanagedId, (int)$productId)) {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }
}
