<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\Image;

use M2E\TikTokShop\Model\ResourceModel\Product\Image\Relation as ImageRelationResource;

class Relation extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ImageRelationResource::class);
    }

    public function getListingProductId(): int
    {
        return (int)$this->getDataByKey(ImageRelationResource::COLUMN_LISTING_PRODUCT_ID);
    }

    public function setListingProductId(int $listingProductId): void
    {
        $this->setData(ImageRelationResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);
    }

    public function getImageId(): int
    {
        return (int)$this->getDataByKey(ImageRelationResource::COLUMN_IMAGE_ID);
    }

    public function setImageId(int $imageId): void
    {
        $this->setData(ImageRelationResource::COLUMN_IMAGE_ID, $imageId);
    }

    public function setCreateDate(\DateTimeInterface $dateTime): void
    {
        $this->setData(ImageRelationResource::COLUMN_CREATE_DATE, $dateTime->format('Y-m-d H:i:s'));
    }
}
