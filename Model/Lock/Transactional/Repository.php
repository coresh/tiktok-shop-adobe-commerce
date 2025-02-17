<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock\Transactional;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional $resource;
    private \M2E\TikTokShop\Model\Lock\TransactionalFactory $entityFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional $resource,
        \M2E\TikTokShop\Model\Lock\TransactionalFactory $entityFactory
    ) {
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
    }

    public function create(\M2E\TikTokShop\Model\Lock\Transactional $lock): void
    {
        $this->resource->save($lock);
    }

    public function findByNick(string $nick): ?\M2E\TikTokShop\Model\Lock\Transactional
    {
        $object = $this->entityFactory->createEmpty();

        $this->resource->load($object, $nick, \M2E\TikTokShop\Model\ResourceModel\Lock\Transactional::COLUMN_NICK);

        if ($object->isObjectNew()) {
            return null;
        }

        return $object;
    }

    // ----------------------------------------

    public function retrieveLock(string $nick): ?int
    {
        $lockId = (int)$this->resource->getConnection()
                                      ->select()
                                      ->from($this->getTableName(), ['id'])
                                      ->where('nick = ?', $nick)
                                      ->forUpdate()
                                      ->query()
                                      ->fetchColumn();

        if (empty($lockId)) {
            return null;
        }

        return $lockId;
    }

    public function startTransaction(): void
    {
        $connection = $this->resource->getConnection();
        $connection->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $connection = $this->resource->getConnection();
        $connection->commit();
    }

    public function lockTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->query("LOCK TABLES `{$this->getTableName()}` WRITE");
    }

    public function unlockTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->query('UNLOCK TABLES');
    }

    // ----------------------------------------

    private function getTableName(): string
    {
        return $this->resource->getMainTable();
    }
}
