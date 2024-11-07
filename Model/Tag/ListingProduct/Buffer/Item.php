<?php

namespace M2E\TikTokShop\Model\Tag\ListingProduct\Buffer;

class Item
{
    /** @var int This is listing_product_id */
    private $productId;
    /** @var array<string, \M2E\TikTokShop\Model\Tag> */
    private $addedTags = [];
    /** @var array<string, \M2E\TikTokShop\Model\Tag> */
    private $removedTags = [];

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function addTag(\M2E\TikTokShop\Model\Tag $tag): void
    {
        unset($this->removedTags[$tag->getErrorCode()]);
        $this->addedTags[$tag->getErrorCode()] = $tag;
    }

    public function removeTag(\M2E\TikTokShop\Model\Tag $tag): void
    {
        unset($this->addedTags[$tag->getErrorCode()]);
        $this->removedTags[$tag->getErrorCode()] = $tag;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return \M2E\TikTokShop\Model\Tag[]
     */
    public function getAddedTags(): array
    {
        return array_values($this->addedTags);
    }

    /**
     * @return \M2E\TikTokShop\Model\Tag[]
     */
    public function getRemovedTags(): array
    {
        return array_values($this->removedTags);
    }
}
