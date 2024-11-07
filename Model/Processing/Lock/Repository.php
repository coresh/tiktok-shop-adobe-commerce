<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Lock;

use M2E\TikTokShop\Model\ResourceModel\Processing\Lock as ProcessingLockResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Processing\Lock\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Processing\Lock\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->tablesHelper = $tablesHelper;
    }

    public function create(\M2E\TikTokShop\Model\Processing\Lock $lock): void
    {
        $lock->save();
    }

    public function remove(\M2E\TikTokShop\Model\Processing\Lock $lock): void
    {
        $lock->delete();
    }

    public function isExist(string $objNick, int $objId): bool
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ProcessingLockResource::COLUMN_OBJECT_NICK, $objNick)
            ->addFieldToFilter(ProcessingLockResource::COLUMN_OBJECT_ID, $objId)
            ->setPageSize(1);

        return !empty($collection->getItems());
    }

    /**
     * @param string $objNick
     * @param int $objId
     *
     * @return \M2E\TikTokShop\Model\Processing\Lock[]
     */
    public function findByObjNameAndObjId(string $objNick, int $objId): array
    {
        return $this->findByParams($objNick, $objId);
    }

    /**
     * @param string $objNick
     *
     * @return \M2E\TikTokShop\Model\Processing\Lock[]
     */
    public function findByObjName(string $objNick): array
    {
        return $this->findByParams($objNick, null);
    }

    /**
     * @param string $objNick
     * @param int|null $objId
     *
     * @return \M2E\TikTokShop\Model\Processing\Lock[]
     */
    private function findByParams(string $objNick, ?int $objId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ProcessingLockResource::COLUMN_OBJECT_NICK, $objNick);

        if ($objId !== null) {
            $collection->addFieldToFilter(ProcessingLockResource::COLUMN_OBJECT_ID, $objId);
        }

        return array_values($collection->getItems());
    }

    public function findByProcessingAndNickAndId(
        \M2E\TikTokShop\Model\Processing $processing,
        string $objNick,
        int $objId
    ): ?\M2E\TikTokShop\Model\Processing\Lock {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ProcessingLockResource::COLUMN_PROCESSING_ID, $processing->getId())
            ->addFieldToFilter(ProcessingLockResource::COLUMN_OBJECT_NICK, $objNick)
            ->addFieldToFilter(ProcessingLockResource::COLUMN_OBJECT_ID, $objId)
            ->setPageSize(1);

        /** @var \M2E\TikTokShop\Model\Processing\Lock $lock */
        $lock = $collection->getFirstItem();
        if (!$lock->isObjectNew()) {
            return $lock;
        }

        return null;
    }

    public function removeAllByProcessing(\M2E\TikTokShop\Model\Processing $processing): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $this->tablesHelper->getFullName(
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_LOCK,
            ),
            [sprintf('`%s` = ?', ProcessingLockResource::COLUMN_PROCESSING_ID) => $processing->getId()],
        );
    }

    /**
     * @return \M2E\TikTokShop\Model\Processing\Lock[]
     */
    public function findMissedLocks(): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->getSelect()->joinLeft(
                [
                    'p' => $this->tablesHelper->getFullName(
                        \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING,
                    ),
                ],
                sprintf('p.id = main_table.%s', ProcessingLockResource::COLUMN_PROCESSING_ID),
                []
            );
        $collection->addFieldToFilter('p.id', ['null' => true]);

        return array_values($collection->getItems());
    }
}
