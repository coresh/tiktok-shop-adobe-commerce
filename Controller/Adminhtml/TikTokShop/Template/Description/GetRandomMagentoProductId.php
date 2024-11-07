<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\Description;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\AbstractDescription;

class GetRandomMagentoProductId extends AbstractDescription
{
    private \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource,
        \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product $productModel,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        $this->listingResource = $listingResource;
        parent::__construct(
            $phpEnvironmentRequest,
            $productModel,
            $templateManager
        );
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getPost('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $productId = $this->getProductIdFromListing($storeId) ?? $this->getProductIdFromMagento();

        if ($productId) {
            $this->setJsonContent([
                'success' => true,
                'product_id' => $productId,
            ]);
        } else {
            $this->setJsonContent([
                'success' => false,
                'message' => __('You don\'t have any products in Magento catalog.'),
            ]);
        }

        return $this->getResult();
    }

    private function getProductIdFromListing($storeId): ?int
    {
        $listingProductCollection = $this->listingProductCollectionFactory->create();
        $listingProductCollection->joinLeft(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf(
                'listing.%s = main_table.%s',
                \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_ID,
                \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_LISTING_ID
            ),
            [\M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_STORE_ID]
        );
        $listingProductCollection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_STORE_ID,
            $storeId
        );
        $collectionSize = $listingProductCollection->getSize();

        if ($collectionSize == 0) {
            return null;
        }

        $listingProductCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(\M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID)
            ->limit(1, $this->calculateOffset($collectionSize));

        $listingProduct = $listingProductCollection->getFirstItem();

        return $listingProduct->getMagentoProductId();
    }

    private function getProductIdFromMagento(): ?int
    {
        $productCollection = $this->productModel->getCollection();
        $collectionSize = $productCollection->getSize();

        if ($collectionSize == 0) {
            return null;
        }

        $productCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('entity_id')
            ->limit(1, $this->calculateOffset($collectionSize));

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productCollection->getFirstItem();

        return $product->getEntityId();
    }

    private function calculateOffset(int $collectionSize): int
    {
        return rand(0, $collectionSize - 1);
    }
}
