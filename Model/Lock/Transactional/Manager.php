<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock\Transactional;

class Manager
{
    private string $nick;

    private bool $isTableLocked = false;
    private bool $isTransactionStarted = false;

    /** @var \M2E\TikTokShop\Model\Lock\Transactional\Repository */
    private Repository $repository;
    private \M2E\TikTokShop\Model\Lock\TransactionalFactory $lockFactory;

    public function __construct(
        string $nick,
        Repository $repository,
        \M2E\TikTokShop\Model\Lock\TransactionalFactory $lockFactory
    ) {
        $this->nick = $nick;
        $this->repository = $repository;
        $this->lockFactory = $lockFactory;
    }

    public function __destruct()
    {
        $this->unlock();
    }

    // ----------------------------------------

    public function getNick(): string
    {
        return $this->nick;
    }

    // ----------------------------------------

    public function lock(): void
    {
        if ($this->getExclusiveLock()) {
            return;
        }

        $this->createExclusiveLock();
        $this->getExclusiveLock();
    }

    public function unlock(): void
    {
        if ($this->isTableLocked) {
            $this->unlockTable();
        }

        if ($this->isTransactionStarted) {
            $this->commitTransaction();
        }
    }

    // ----------------------------------------

    private function getExclusiveLock(): bool
    {
        $this->startTransaction();

        $lockId = $this->repository->retrieveLock($this->nick);
        if ($lockId !== null) {
            return true;
        }

        $this->commitTransaction();

        return false;
    }

    private function createExclusiveLock(): void
    {
        $this->lockTable();

        $lock = $this->repository->findByNick($this->nick);
        if ($lock === null) {
            $lock = $this->lockFactory->create($this->nick);
            $this->repository->create($lock);
        }

        $this->unlockTable();
    }

    // ----------------------------------------

    private function startTransaction(): void
    {
        $this->repository->startTransaction();

        $this->isTransactionStarted = true;
    }

    private function commitTransaction(): void
    {
        $this->repository->commitTransaction();

        $this->isTransactionStarted = false;
    }

    // ----------------------------------------

    private function lockTable(): void
    {
        $this->repository->lockTable();

        $this->isTableLocked = true;
    }

    private function unlockTable(): void
    {
        $this->repository->unlockTable();

        $this->isTableLocked = false;
    }
}
