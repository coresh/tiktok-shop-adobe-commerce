<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct;

use M2E\TikTokShop\Model\ResourceModel\GlobalProduct as GlobalProductResource;
use M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku as GlobalProductVariantSkuResource;
use M2E\TikTokShop\Model\ResourceModel\GlobalProduct;

class Repository
{
    private GlobalProductResource $globalProductResource;
    private GlobalProductVariantSkuResource $globalProductVariantSkuResource;
    private GlobalProduct\CollectionFactory $globalProductCollectionFactory;
    private GlobalProduct\VariantSku\CollectionFactory $globalProductVariantCollectionFactory;

    public function __construct(
        GlobalProductResource $globalProductResource,
        GlobalProductVariantSkuResource $globalProductVariantSkuResource,
        GlobalProduct\CollectionFactory $globalProductCollectionFactory,
        GlobalProduct\VariantSku\CollectionFactory $globalProductVariantCollectionFactory
    ) {
        $this->globalProductResource = $globalProductResource;
        $this->globalProductCollectionFactory = $globalProductCollectionFactory;
        $this->globalProductVariantSkuResource = $globalProductVariantSkuResource;
        $this->globalProductVariantCollectionFactory = $globalProductVariantCollectionFactory;
    }

    public function create(\M2E\TikTokShop\Model\GlobalProduct $globalProduct): \M2E\TikTokShop\Model\GlobalProduct
    {
        $this->globalProductResource->save($globalProduct);

        return $globalProduct;
    }

    public function save(\M2E\TikTokShop\Model\GlobalProduct $globalProduct): \M2E\TikTokShop\Model\GlobalProduct
    {
        $this->globalProductResource->save($globalProduct);

        return $globalProduct;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\GlobalProduct
    {
        $collection = $this->globalProductCollectionFactory->create();
        $collection->addFieldToFilter(
            GlobalProductResource::COLUMN_ID,
            ['eq' => $id]
        );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    public function get(int $id): \M2E\TikTokShop\Model\GlobalProduct
    {
        $globalProduct = $this->find($id);
        if ($globalProduct === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Global product not found');
        }

        return $globalProduct;
    }

    public function findByAccountIdAndMagentoProductId(
        int $accountId,
        int $magentoProductId
    ): ?\M2E\TikTokShop\Model\GlobalProduct {
        $collection = $this->globalProductCollectionFactory->create();
        $collection->addFieldToFilter(
            GlobalProductResource::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );
        $collection->addFieldToFilter(
            GlobalProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId]
        );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    // ----------------------------------------

    public function createVariantSku(VariantSku $globalVariantSku): VariantSku
    {
        $this->globalProductVariantSkuResource->save($globalVariantSku);

        return $globalVariantSku;
    }

    public function saveVariantSku(VariantSku $globalVariantSku): VariantSku
    {
        $this->globalProductVariantSkuResource->save($globalVariantSku);

        return $globalVariantSku;
    }

    /**
     * @return \M2E\TikTokShop\Model\GlobalProduct\VariantSku[]
     */
    public function getVariantsByGlobalProductId(int $globalProductId): array
    {
        $collection = $this->globalProductVariantCollectionFactory->create();
        $collection->addFieldToFilter(
            GlobalProductVariantSkuResource::COLUMN_GLOBAL_PRODUCT_ID,
            ['eq' => $globalProductId]
        );

        return array_values($collection->getItems());
    }
}
