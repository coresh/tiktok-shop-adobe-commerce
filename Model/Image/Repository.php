<?php

namespace M2E\TikTokShop\Model\Image;

use M2E\TikTokShop\Model\ResourceModel\Image as ImageResource;
use M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation as ImageRelationResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Image\CollectionFactory $collectionFactory;
    private ImageResource $imageResource;
    private ImageRelationResource $imageRelationResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation $imageRelationResource,
        \M2E\TikTokShop\Model\ResourceModel\Image $imageResource,
        \M2E\TikTokShop\Model\ResourceModel\Image\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->imageResource = $imageResource;
        $this->imageRelationResource = $imageRelationResource;
    }

    /**
     * @return \M2E\TikTokShop\Model\Image[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findImagesWithProductRelation(int $listingProductId, string $type): array
    {
        $collection = $this->collectionFactory->create();
        $collection->join(
            ['relation' => $this->imageRelationResource->getMainTable()],
            sprintf(
                '`relation`.`%s` = `main_table`.`%s`',
                ImageRelationResource::COLUMN_IMAGE_ID,
                \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_ID,
            ),
            [ImageRelationResource::COLUMN_LISTING_PRODUCT_ID => ImageRelationResource::COLUMN_LISTING_PRODUCT_ID]
        );

        $collection->addFieldToFilter(ImageRelationResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);
        $collection->addFieldToFilter(ImageResource::COLUMN_TYPE, $type);

        return array_values($collection->getItems());
    }

    public function findByHashAndType(string $hash, string $type): ?\M2E\TikTokShop\Model\Image
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ImageResource::COLUMN_HASH, $hash);
        $collection->addFieldToFilter(ImageResource::COLUMN_TYPE, $type);

        $entity = $collection->getFirstItem();

        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    public function findByUri(string $uri): ?\M2E\TikTokShop\Model\Image
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ImageResource::COLUMN_URI, $uri);

        $entity = $collection->getFirstItem();

        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    public function save(\M2E\TikTokShop\Model\Image $image): \M2E\TikTokShop\Model\Image
    {
        $now = \M2E\Core\Helper\Date::createCurrentGmt();
        if ($image->isObjectNew()) {
            $image->setCreateDate($now);
        }

        $image->setUpdateDate($now);

        $this->imageResource->save($image);

        return $image;
    }
}
