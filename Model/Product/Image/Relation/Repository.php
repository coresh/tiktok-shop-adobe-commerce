<?php

namespace M2E\TikTokShop\Model\Product\Image\Relation;

use M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation as RelationResource;

class Repository
{
    private RelationResource\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\Product\Image\RelationFactory $relationEntityFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation $resource;
    private \M2E\TikTokShop\Model\ResourceModel\Image $imageResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation $resource,
        RelationResource\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\Product\Image\RelationFactory $relationEntityFactory,
        \M2E\TikTokShop\Model\ResourceModel\Image $imageResource
    ) {
        $this->resource = $resource;
        $this->relationEntityFactory = $relationEntityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->imageResource = $imageResource;
    }

    public function create(\M2E\TikTokShop\Model\Product\Image\Relation $relation)
    {
        $this->resource->save($relation);
    }

    public function save(\M2E\TikTokShop\Model\Product\Image\Relation $relation)
    {
        $this->resource->save($relation);
    }

    public function delete(\M2E\TikTokShop\Model\Product\Image\Relation $relation)
    {
        $this->resource->delete($relation);
    }

    public function createIfNotExists(int $listingProductId, int $imageId): void
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(RelationResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);
        $collection->addFieldToFilter(RelationResource::COLUMN_IMAGE_ID, $imageId);

        /** @var \M2E\TikTokShop\Model\Product\Image\Relation $item */
        $item = $collection->getFirstItem();
        if ($item->getId() !== null) {
            return;
        }

        $entity = $this->relationEntityFactory->create();
        $entity->setListingProductId($listingProductId);
        $entity->setImageId($imageId);
        $entity->setCreateDate(\M2E\Core\Helper\Date::createCurrentGmt());

        $this->create($entity);
    }

    public function deleteByListingProductId(int $listingProductId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RelationResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);

        /** @var \M2E\TikTokShop\Model\Product\Image\Relation $item */
        foreach ($collection as $item) {
            $this->delete($item);
        }
    }

    /**
     * @return \M2E\TikTokShop\Model\Product\Image\Relation[]
     */
    public function getByListingProductId(int $listingProductId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            RelationResource::COLUMN_LISTING_PRODUCT_ID,
            ['eq' => $listingProductId]
        );

        return array_values($collection->getItems());
    }

    public function findByUriAndListingProductId(
        int $productId,
        string $uri
    ): ?\M2E\TikTokShop\Model\Product\Image\Relation {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            RelationResource::COLUMN_LISTING_PRODUCT_ID,
            ['eq' => $productId]
        );

        $collection->getSelect()->joinInner(
            ['image' => $this->imageResource->getMainTable()],
            sprintf(
                'image.%s = main_table.%s',
                \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_ID,
                RelationResource::COLUMN_IMAGE_ID
            ),
            []
        );
        $collection->getSelect()->where(
            sprintf('image.%s = ?', \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_URI),
            $uri
        );

        $res = $collection->getFirstItem();

        if ($res->isObjectNew()) {
            return null;
        }

        return $res;
    }
}
