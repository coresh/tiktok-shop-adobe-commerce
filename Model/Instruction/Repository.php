<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Instruction;

use M2E\TikTokShop\Model\ResourceModel\Instruction as InstructionResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ProductResource;

class Repository
{
    private InstructionResource\CollectionFactory $collectionFactory;
    private InstructionResource $resource;
    private \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        InstructionResource $resource,
        InstructionResource\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource,
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->listingProductResource = $listingProductResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function create(\M2E\TikTokShop\Model\Instruction $instruction): void
    {
        $instruction->save();
    }

    /**
     * @param list<array{listing_product_id:int, type: string, initiator: int, priority: int, skip_until?:\DateTime}>
     *     $batchData
     *
     * @return void
     */
    public function createMultiple(array $batchData): void
    {
        if (empty($batchData)) {
            return;
        }

        $listingsProductsIds = [];
        foreach ($batchData as $instructionData) {
            $listingsProductsIds[] = $instructionData['listing_product_id'];
        }

        $listingsProductsCollection = $this->listingProductCollectionFactory->create();
        $instructionSelectExpression = new \Zend_Db_Expr(
            "IFNULL(CONCAT('[\"', GROUP_CONCAT(DISTINCT lpi.type SEPARATOR '\",\"'), '\"]'), '[]')",
        );

        $listingsProductsCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns([
                'id' => 'main_table.id',
            ])
            ->joinLeft(
                [
                    'lpi' => $this->resource->getMainTable(),
                ],
                'lpi.listing_product_id = main_table.id',
                ['instruction_json_types' => $instructionSelectExpression],
            )
            ->where('main_table.id IN (?)', array_unique($listingsProductsIds))
            ->group('main_table.id')
            ->order('main_table.id');

        $insertData = [];
        foreach ($batchData as $index => $instructionData) {
            /** @var \Magento\Framework\DataObject $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($instructionData['listing_product_id']);
            if ($listingProduct === null) {
                unset($batchData[$index]);
                continue;
            }

            $encodedInstructionTypes = $listingProduct->getData('instruction_json_types');
            $instructionTypes = (array)json_decode($encodedInstructionTypes, true);

            if (in_array($instructionData['type'], $instructionTypes, true)) {
                unset($batchData[$index]);
                continue;
            }

            // {listing_product_id:int, type: string, initiator: string, priority: int, skip_until?:\DateTime}
            $insertData[] = [
                InstructionResource::COLUMN_LISTING_PRODUCT_ID => $instructionData['listing_product_id'],
                InstructionResource::COLUMN_TYPE => $instructionData['type'],
                InstructionResource::COLUMN_INITIATOR => $instructionData['initiator'],
                InstructionResource::COLUMN_PRIORITY => $instructionData['priority'],
                InstructionResource::COLUMN_SKIP_UNTIL => (isset($instructionData['skip_until']) ?
                    $instructionData['skip_until']->format('Y-m-d H:i:s') : null),
                InstructionResource::COLUMN_CREATE_DATE => \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
            ];
        }

        if (empty($insertData)) {
            return;
        }

        $this->resource
            ->getConnection()
            ->insertMultiple(
                $this->resource->getMainTable(),
                $insertData,
            );
    }

    /**
     * @param int[] $listingProductsIds
     *
     * @return \M2E\TikTokShop\Model\Instruction[]
     */
    public function findByListingProducts(array $listingProductsIds, ?\DateTime $excludeUntil): array
    {
        $collection = $this->collectionFactory->create();

        $this->addSkipUntilFilter($collection, $excludeUntil);

        $collection->addFieldToFilter(InstructionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductsIds);

        return array_values($collection->getItems());
    }

    /**
     * @param int $limit
     * @param \DateTime|null $excludeUntil
     *
     * @return int[]
     * @throws \Exception
     */
    public function findListingProductIdsByPriority(int $limit, ?\DateTime $excludeUntil): array
    {
        $collection = $this->collectionFactory->create();

        $this->addSkipUntilFilter($collection, $excludeUntil);

        $collection->setOrder('MAX(main_table.priority)', 'DESC');
        $collection->setOrder('MIN(main_table.create_date)', 'ASC');

        $collection->getSelect()->limit($limit);
        $collection->getSelect()->group('main_table.' . InstructionResource::COLUMN_LISTING_PRODUCT_ID);
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns('main_table.' . InstructionResource::COLUMN_LISTING_PRODUCT_ID);

        return array_map(
            static function ($id) {
                return (int)$id;
            },
            $collection->getColumnValues(InstructionResource::COLUMN_LISTING_PRODUCT_ID),
        );
    }

    public function getCountByInitiator(string $initiator): int
    {
        $collection = $this->collectionFactory->create();

        $this->addSkipUntilFilter($collection, null);

        return $collection->addFieldToFilter(InstructionResource::COLUMN_INITIATOR, $initiator)
                                               ->getSize();
    }

    /**
     * @param int[] $ids
     *
     * @return void
     */
    public function removeByIds(array $ids): void
    {
        $this->doRemoveByIds($ids);
    }

    public function removeByListingProduct(int $listingProductId): void
    {
        $columnName = InstructionResource::COLUMN_LISTING_PRODUCT_ID;

        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                [
                    "$columnName = ?" => $listingProductId,
                ]
            );
    }

    public function removeOld(\DateTime $borderDate): void
    {
        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                ['create_date < ?' => $borderDate->format('Y-m-d')],
            );
    }

    public function removeWithoutListingProduct(): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['second_table' => $this->listingProductResource->getMainTable()],
            sprintf('main_table.%s = second_table.id', InstructionResource::COLUMN_LISTING_PRODUCT_ID),
        );
        $collection->getSelect()->where(sprintf('second_table.%s IS NULL', ProductResource::COLUMN_ID));
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(sprintf('main_table.%s', InstructionResource::COLUMN_ID));

        $this->doRemoveByIds($collection->getColumnValues(InstructionResource::COLUMN_ID));
    }

    private function addSkipUntilFilter(InstructionResource\Collection $collection, ?\DateTime $excludeUntil): void
    {
        if ($excludeUntil === null) {
            $excludeUntil = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        }

        $columName = InstructionResource::COLUMN_SKIP_UNTIL;

        $collection->getSelect()->where(
            "$columName IS NULL OR ? > $columName",
            $excludeUntil->format('Y-m-d H:i:s'),
        );
    }

    private function doRemoveByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                [
                    'id IN (?)' => $ids,
                ],
            );
    }
}
