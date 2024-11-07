<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class Map extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->productCollectionFactory = $productCollectionFactory;
        $this->listingOtherRepository = $listingOtherRepository;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id'); // Magento
        $productOtherId = $this->getRequest()->getParam('other_product_id');

        if (!$productId || !$productOtherId) {
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

        $listingOther = $this->listingOtherRepository->get($productOtherId);

        $listingOther->mapToMagentoProduct((int)$productId);

        $this->listingOtherRepository->save($listingOther);

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }
}
