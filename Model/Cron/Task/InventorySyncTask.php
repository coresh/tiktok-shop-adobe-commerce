<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task;

use M2E\TikTokShop\Model\Shop;

class InventorySyncTask extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'inventory/sync';

    private const SYNC_INTERVAL_30_MINUTES_IN_SECONDS = 1800;

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    protected int $intervalInSeconds = 300;

    private \M2E\TikTokShop\Model\Processing\Runner $processingRunner;
    private \M2E\TikTokShop\Model\Processing\Lock\Repository $lockRepository;
    private \M2E\TikTokShop\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Processing\Runner $processingRunner,
        \M2E\TikTokShop\Model\Processing\Lock\Repository $lockRepository,
        \M2E\TikTokShop\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory,
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger,
        \M2E\TikTokShop\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource,
        );
        $this->accountRepository = $accountRepository;
        $this->processingRunner = $processingRunner;
        $this->lockRepository = $lockRepository;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\TikTokShop\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $synchronizationLog->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $currentDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        foreach ($this->accountRepository->findActiveWithEnabledInventorySync() as $account) {
            foreach ($account->getShops() as $shop) {
                if (
                    $shop->getInventoryLastSyncDate() !== null
                    && $shop->getInventoryLastSyncDate()->modify(
                        '+ ' . self::SYNC_INTERVAL_30_MINUTES_IN_SECONDS . ' seconds',
                    ) > $currentDate
                ) {
                    continue;
                }

                if ($this->lockRepository->isExist(Shop::LOCK_NICK, $shop->getId())) {
                    continue;
                }

                $this->getOperationHistory()->addText(
                    "Starting Account (Shop) '{$account->getTitle()} ({$shop->getShopId()})'",
                );
                $this->getOperationHistory()->addTimePoint(
                    $timePointId = __METHOD__ . 'process' . $account->getId() . $shop->getShopId(),
                    "Process Account '{$account->getTitle()} ({$shop->getShopId()})'",
                );

                // ----------------------------------------

                try {
                    if ($shop->getInventoryLastSyncDate() === null) {
                        $initiator = $this->processingInitiatorFactory->createByCreateDate($account, $shop);
                    } else {
                        $initiator = $this->processingInitiatorFactory->createByUpdateDate(
                            $account,
                            $shop,
                            $shop->getInventoryLastSyncDate()
                        );
                    }
                    $this->processingRunner->run($initiator);
                } catch (\Throwable $e) {
                    $this->getOperationHistory()->addText(
                        "Error '{$account->getTitle()} ({$shop->getShopId()})'. Message: {$e->getMessage()}",
                    );
                }

                // ----------------------------------------

                $this->getOperationHistory()->saveTimePoint($timePointId);
            }
        }
    }
}
