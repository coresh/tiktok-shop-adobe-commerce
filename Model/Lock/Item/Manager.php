<?php

namespace M2E\TikTokShop\Model\Lock\Item;

class Manager
{
    public const DEFAULT_MAX_INACTIVE_TIME = 900;

    private string $nick;
    private \M2E\TikTokShop\Model\Lock\ItemFactory $lockItemFactory;
    private ManagerFactory $lockItemManagerFactory;
    /** @var \M2E\TikTokShop\Model\Lock\Item\Repository */
    private Repository $repository;

    public function __construct(
        string $nick,
        \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory,
        \M2E\TikTokShop\Model\Lock\ItemFactory $lockItemFactory,
        \M2E\TikTokShop\Model\Lock\Item\Repository $repository
    ) {
        $this->nick = $nick;
        $this->lockItemFactory = $lockItemFactory;
        $this->lockItemManagerFactory = $lockItemManagerFactory;
        $this->repository = $repository;
    }

    // ----------------------------------------

    public function getNick(): string
    {
        return $this->nick;
    }

    // ----------------------------------------

    public function isExist(): bool
    {
        return $this->getLockItemObject() !== null;
    }

    public function create(?string $parentNick = null): self
    {
        $parentLockItem = null;
        if ($parentNick !== null) {
            $parentLockItem = $this->repository->findByNick($parentNick);
        }

        $lockModel = $this->lockItemFactory->create(
            $this->nick,
            $parentLockItem !== null ? $parentLockItem->getId() : null
        );

        $this->repository->create($lockModel);

        return $this;
    }

    public function remove(): bool
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return false;
        }

        foreach ($this->repository->findByParentId((int)$lockItem->getId()) as $childLockItem) {
            $childManager = $this->lockItemManagerFactory->create(
                $childLockItem->getNick()
            );
            $childManager->remove();
        }

        $this->repository->remove($lockItem);

        return true;
    }

    // ---------------------------------------

    public function isInactiveMoreThanSeconds(int $maxInactiveInterval): bool
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return true;
        }

        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        return $lockItem->getUpdateDate()->getTimestamp() < ($currentDate->getTimestamp() - $maxInactiveInterval);
    }

    public function activate(): void
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf('Lock Item with nick "%s" does not exist.', $this->nick)
            );
        }

        if ($lockItem->getParentId() !== null) {
            $parentLockItem = $this->repository->findById($lockItem->getParentId());

            if ($parentLockItem !== null) {
                $parentManager = $this->lockItemManagerFactory->create(
                    $parentLockItem->getNick()
                );
                $parentManager->activate();
            }
        }

        $lockItem->actualize();

        $this->repository->save($lockItem);
    }

    // ----------------------------------------

    public function setContentData(array $data): void
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf('Lock Item with nick "%s" does not exist.', $this->nick)
            );
        }

        $lockItem->setContentData($data);

        $this->repository->save($lockItem);
    }

    public function getContentData(): array
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf('Lock Item with nick "%s" does not exist.', $this->nick)
            );
        }

        return $lockItem->getContentData();
    }

    // ----------------------------------------

    private function getLockItemObject(): ?\M2E\TikTokShop\Model\Lock\Item
    {
        return $this->repository->findByNick($this->nick);
    }
}
