<?php

namespace M2E\TikTokShop\Model\Lock\Item;

class Manager
{
    public const DEFAULT_MAX_INACTIVE_TIME = 900;

    private string $nick;
    private \M2E\TikTokShop\Model\ResourceModel\Lock\Item\CollectionFactory $lockItemCollectionFactory;
    private \M2E\TikTokShop\Model\Lock\ItemFactory $lockItemFactory;
    private ManagerFactory $lockItemManagerFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory,
        \M2E\TikTokShop\Model\Lock\ItemFactory $lockItemFactory,
        \M2E\TikTokShop\Model\ResourceModel\Lock\Item\CollectionFactory $lockItemCollectionFactory,
        string $nick
    ) {
        $this->lockItemCollectionFactory = $lockItemCollectionFactory;
        $this->nick = $nick;
        $this->lockItemFactory = $lockItemFactory;
        $this->lockItemManagerFactory = $lockItemManagerFactory;
    }

    // ----------------------------------------

    public function getNick(): string
    {
        return $this->nick;
    }

    // ----------------------------------------

    public function create($parentNick = null): self
    {
        $parentLockItem = $this->lockItemFactory->create();
        if ($parentNick !== null) {
            $parentLockItem->load($parentNick, 'nick');
        }

        $lockModel = $this->lockItemFactory->create();
        $lockModel->setNick($this->nick)
                  ->setParentId($parentLockItem->getId());

        $lockModel->save();

        return $this;
    }

    public function remove(): bool
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return false;
        }

        $childLockItemCollection = $this->lockItemCollectionFactory->create();
        $childLockItemCollection->addFieldToFilter('parent_id', $lockItem->getId());

        /** @var \M2E\TikTokShop\Model\Lock\Item[] $childLockItems */
        $childLockItems = $childLockItemCollection->getItems();

        foreach ($childLockItems as $childLockItem) {
            $childManager = $this->lockItemManagerFactory->create(
                $childLockItem->getNick()
            );
            $childManager->remove();
        }

        $lockItem->delete();

        return true;
    }

    // ---------------------------------------

    public function isExist(): bool
    {
        return $this->getLockItemObject() !== null;
    }

    public function isInactiveMoreThanSeconds($maxInactiveInterval): bool
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return true;
        }

        $currentDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        $updateDate = \M2E\TikTokShop\Helper\Date::createDateGmt($lockItem->getUpdateDate());

        return $updateDate->getTimestamp() < ($currentDate->getTimestamp() - $maxInactiveInterval);
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
            $parentLockItem = $this->lockItemFactory->create()->load($lockItem->getParentId());

            if ($parentLockItem->getId()) {
                $parentManager = $this->lockItemManagerFactory->create(
                    $parentLockItem->getNick()
                );
                $parentManager->activate();
            }
        }

        $lockItem->setDataChanges(true);
        $lockItem->save();
    }

    // ----------------------------------------

    public function addContentData($key, $value): bool
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf('Lock Item with nick "%s" does not exist.', $this->nick)
            );
        }

        $data = $lockItem->getContentData();
        if (!empty($data)) {
            $data = \M2E\TikTokShop\Helper\Json::decode($data);
        } else {
            $data = [];
        }

        $data[$key] = $value;

        $lockItem->setData('data', \M2E\TikTokShop\Helper\Json::encode($data));
        $lockItem->save();

        return true;
    }

    public function setContentData(array $data): bool
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf('Lock Item with nick "%s" does not exist.', $this->nick)
            );
        }

        $lockItem->setData('data', \M2E\TikTokShop\Helper\Json::encode($data));
        $lockItem->save();

        return true;
    }

    // ---------------------------------------

    public function getContentData($key = null)
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf('Lock Item with nick "%s" does not exist.', $this->nick)
            );
        }

        if ($lockItem->getData('data') == '') {
            return null;
        }

        $data = \M2E\TikTokShop\Helper\Json::decode($lockItem->getContentData());
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    // ----------------------------------------

    private function getLockItemObject(): ?\M2E\TikTokShop\Model\Lock\Item
    {
        $lockItemCollection = $this->lockItemCollectionFactory->create();
        $lockItemCollection->addFieldToFilter('nick', $this->nick);

        /** @var \M2E\TikTokShop\Model\Lock\Item $lockItem */
        $lockItem = $lockItemCollection->getFirstItem();

        return $lockItem->getId() ? $lockItem : null;
    }
}
