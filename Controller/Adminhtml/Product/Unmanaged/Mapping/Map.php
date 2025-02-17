<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Mapping;

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
        $accountId = $this->getRequest()->getParam('account_id');

        if (!$productId || !$productUnmanagedId) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);

        $magentoCatalogProductModel = $collection->getFirstItem();
        if ($magentoCatalogProductModel->isEmpty()) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $productId = $magentoCatalogProductModel->getId();

        if (!$this->unmanagedMappingService->manualMapProduct((int)$productUnmanagedId, (int)$productId)) {
            $this->getMessageManager()->addErrorMessage(
                'Product Variation mismatch: please ensure both products share identical variation structures (e.g., size, color) before attempting to link them.'
            );

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
    }
}
