<?php

namespace M2E\TikTokShop\Model\Tag\ListingProduct;

class Repository
{
    /** @var \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation\CollectionFactory */
    private $relationCollectionFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Tag\CollectionFactory */
    private $tagCollectionFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation */
    private $relationResource;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Product */
    private $listingProductResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation\CollectionFactory $relationCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation $relationResource,
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource
    ) {
        $this->relationCollectionFactory = $relationCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->relationResource = $relationResource;
        $this->listingProductResource = $listingProductResource;
    }

    /**
     * @param int[] $ids
     *
     * @return \M2E\TikTokShop\Model\Tag\ListingProduct\Relation[]
     */
    public function findRelationsByProductIds(array $ids): array
    {
        $collection = $this->relationCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation::COLUMN_LISTING_PRODUCT_ID,
            [
                'in' => array_unique($ids),
            ]
        );

        $result = [];
        /** @var \M2E\TikTokShop\Model\Tag\ListingProduct\Relation $item */
        foreach ($collection as $item) {
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @return \M2E\TikTokShop\Model\Tag\Entity[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTagEntitiesWithoutHasErrorsTag(): array
    {
        $collection = $this->tagCollectionFactory->create();

        $collection->getSelect()->join(
            ['rel' => $this->relationResource->getMainTable()],
            'main_table.id = rel.tag_id'
        );

        $collection->getSelect()->join(
            ['lp' => $this->listingProductResource->getMainTable()],
            'rel.listing_product_id = lp.id'
        );

        $collection->distinct(true);

        $collection->getSelect()->reset('columns');
        $collection->getSelect()->columns([
            'main_table.' . \M2E\TikTokShop\Model\ResourceModel\Tag::COLUMN_ID,
            'main_table.' . \M2E\TikTokShop\Model\ResourceModel\Tag::COLUMN_TEXT,
            'main_table.' . \M2E\TikTokShop\Model\ResourceModel\Tag::COLUMN_ERROR_CODE,
            'main_table.' . \M2E\TikTokShop\Model\ResourceModel\Tag::COLUMN_CREATE_DATE,
        ]);

        $collection->getSelect()->where(
            \M2E\TikTokShop\Model\ResourceModel\Tag::COLUMN_ERROR_CODE . ' != ?',
            \M2E\TikTokShop\Model\Tag::HAS_ERROR_ERROR_CODE
        );

        return $collection->getAll();
    }
}
