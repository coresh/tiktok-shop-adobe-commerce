<?php

namespace M2E\TikTokShop\Model\Category\Tree;

use M2E\TikTokShop\Model\Category\Tree;
use M2E\TikTokShop\Model\ResourceModel\Category\Tree as CategoryTreeResource;

class Repository
{
    private CategoryTreeResource\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Category\Tree\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Tree[]
     */
    public function getRootCategories(int $shopId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SHOP_ID,
            ['eq' => $shopId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['null' => true]
        );

        return array_values($collection->getItems());
    }

    public function getCategoryByShopIdAndCategoryId(int $shopId, string $categoryId): ?Tree
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SHOP_ID,
            ['eq' => $shopId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            ['eq' => $categoryId]
        );

        /** @var Tree $entity */
        $entity = $collection->getFirstItem();

        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    /**
     * @param int $shopId
     * @param int $parentCategoryId
     *
     * @return Tree[]
     */
    public function getChildCategories(int $shopId, int $parentCategoryId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SHOP_ID,
            ['eq' => $shopId]
        );
        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['eq' => $parentCategoryId]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param Tree $entity
     *
     * @return Tree[]
     */
    public function getParents(Tree $entity): array
    {
        $ancestors = $this->getRecursivelyParents($entity);

        return array_reverse($ancestors);
    }

    /**
     * @param Tree[] $ancestors
     *
     * @return Tree[]
     */
    private function getRecursivelyParents(Tree $child, array $ancestors = []): array
    {
        if ($child->getParentCategoryId() === null) {
            return $ancestors;
        }

        $parent = $this->getCategoryByShopIdAndCategoryId(
            $child->getShopId(),
            $child->getParentCategoryId()
        );
        if ($parent === null) {
            return $ancestors;
        }

        $ancestors[] = $parent;

        return $this->getRecursivelyParents($parent, $ancestors);
    }

    /**
     * @param \M2E\TikTokShop\Model\Category\Tree[] $categories
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function batchInsert(array $categories): void
    {
        $insertData = [];
        foreach ($categories as $category) {
            $insertData[] = [
                CategoryTreeResource::COLUMN_SHOP_ID => $category->getShopId(),
                CategoryTreeResource::COLUMN_CATEGORY_ID => $category->getCategoryId(),
                CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID => $category->getParentCategoryId(),
                CategoryTreeResource::COLUMN_TITLE => $category->getTitle(),
                CategoryTreeResource::COLUMN_IS_LEAF => $category->isLeaf(),
                CategoryTreeResource::COLUMN_PERMISSION_STATUSES => json_encode($category->getPermissionStatuses()),
            ];
        }

        $collection = $this->collectionFactory->create();
        $resource = $collection->getResource();

        foreach (array_chunk($insertData, 500) as $chunk) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $chunk);
        }
    }

    public function deleteByShopId(int $shopId): void
    {
        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();
        $connection->delete(
            $collection->getMainTable(),
            sprintf('%s = %s', CategoryTreeResource::COLUMN_SHOP_ID, $shopId)
        );
    }

    /**
     * @return Tree[]
     */
    public function searchByTitleOrId(int $shopId, string $query, int $limit): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SHOP_ID,
            ['eq' => $shopId]
        );

        $collection->addFieldToFilter(
            [CategoryTreeResource::COLUMN_TITLE, CategoryTreeResource::COLUMN_CATEGORY_ID],
            [['like' => "%$query%"], ['like' => "%$query%"]]
        );

        $collection->getSelect()->order([
            sprintf('%s DESC', CategoryTreeResource::COLUMN_IS_LEAF),
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID
        ]);

        $collection->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return Tree[]
     */
    public function getChildren(int $shopId, int $parentCategoryId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            ['eq' => $parentCategoryId]
        );

        $collection->addFieldToFilter(
            CategoryTreeResource::COLUMN_SHOP_ID,
            ['eq' => $shopId]
        );

        $collection->getSelect()->order([
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID
        ]);

        return array_values($collection->getItems());
    }
}
