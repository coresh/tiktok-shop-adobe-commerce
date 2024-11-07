<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class LockManager
{
    private const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 1800; // 30 min

    private \M2E\TikTokShop\Model\Product $listingProduct;
    private int $initiator;
    private int $logsActionId;
    private int $logsAction;

    private \M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager;
    private \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService
    ) {
        $this->lockItemManagerFactory = $lockItemManagerFactory;
        $this->listingLogService = $listingLogService;
    }

    // ----------------------------------------

    public function setListingProduct(\M2E\TikTokShop\Model\Product $listingProduct): self
    {
        $this->listingProduct = $listingProduct;

        return $this;
    }

    public function setInitiator(int $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function setLogsActionId(int $logsActionId): self
    {
        $this->logsActionId = $logsActionId;

        return $this;
    }

    public function setLogsAction(int $logsAction): self
    {
        $this->logsAction = $logsAction;

        return $this;
    }

    // ----------------------------------------

    public function isLocked(): bool
    {
        if (!$this->getLockItemManager()->isExist()) {
            return false;
        }

        if ($this->getLockItemManager()->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->getLockItemManager()->remove();

            return false;
        }

        return true;
    }

    public function checkLocking(): bool
    {
        if (!$this->isLocked()) {
            return false;
        }

        $this->listingLogService->addProduct(
            $this->listingProduct,
            $this->initiator,
            $this->logsAction,
            $this->logsActionId,
            (string)\__('Another Action is being processed. Try again when the Action is completed.'),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
        );

        return true;
    }

    // ----------------------------------------

    public function lock(): void
    {
        $this->getLockItemManager()->create();
    }

    public function unlock(): void
    {
        $this->getLockItemManager()->remove();
    }

    // ----------------------------------------

    private function getLockItemManager(): \M2E\TikTokShop\Model\Lock\Item\Manager
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->lockItemManager)) {
            return $this->lockItemManager;
        }

        $this->lockItemManager = $this->lockItemManagerFactory->create(
            'listing_product_' . $this->listingProduct->getId(),
        );

        return $this->lockItemManager;
    }
}
