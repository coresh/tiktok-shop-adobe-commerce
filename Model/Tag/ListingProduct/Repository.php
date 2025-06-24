<?php

namespace M2E\TikTokShop\Model\Tag\ListingProduct;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Tag $tagResource;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
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
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\ResourceModel\Tag $tagResource
    ) {
        $this->tagResource = $tagResource;
        $this->productRepository = $productRepository;
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

    /**
     * @return \M2E\Core\Model\Dashboard\ProductIssues\Issue[]
     */
    public function getTopIssues(int $limit): array
    {
        return $this->getGroupedIssues($limit);
    }

    private function getGroupedIssues(int $limit): array
    {
        $totalCountOfListingProducts = $this->productRepository->getTotalCountOfListingProducts();

        $collection = $this->relationCollectionFactory->create();

        $collection->join(
            ['tag' => $this->tagResource->getMainTable()],
            'main_table.tag_id = tag.id'
        );

        $collection->join(
            ['lp' => $this->listingProductResource->getMainTable()],
            'main_table.listing_product_id = lp.id'
        );

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns([
            'total' => new \Magento\Framework\DB\Sql\Expression('COUNT(*)'),
            'tag_id' => 'tag.id',
            'tag_text' => 'tag.text',
        ]);
        $collection->getSelect()->where('tag.error_code != ?', \M2E\TikTokShop\Model\Tag::HAS_ERROR_ERROR_CODE);
        $collection->getSelect()->group('main_table.tag_id');
        $collection->getSelect()->order('total DESC');
        $collection->getSelect()->limit($limit);

        $queryData = $collection->getSelect()->query()->fetchAll();

        $issues = [];
        foreach ($queryData as $item) {
            $total = (int)$item['total'];
            $impactRate = $total * 100 / $totalCountOfListingProducts;
            $issues[] = new \M2E\Core\Model\Dashboard\ProductIssues\Issue(
                (int)$item['tag_id'],
                $item['tag_text'],
                $total,
                (float)$impactRate
            );
        }

        return $issues;
    }
}
