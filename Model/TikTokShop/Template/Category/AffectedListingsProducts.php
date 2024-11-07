<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Template\Category;

class AffectedListingsProducts extends \M2E\TikTokShop\Model\Template\AffectedListingsProductsAbstract
{
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function loadListingProductCollection(
        array $filters = []
    ): \M2E\TikTokShop\Model\ResourceModel\Product\Collection {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_TEMPLATE_CATEGORY_ID,
            ['eq' => $this->getModel()->getId()]
        );

        return $collection;
    }
}
