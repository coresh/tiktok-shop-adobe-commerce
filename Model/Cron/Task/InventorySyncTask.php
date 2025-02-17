<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task;

use M2E\TikTokShop\Model\Shop;

class InventorySyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'inventory/sync';

    private const SYNC_INTERVAL_30_MINUTES_IN_SECONDS = 1800;

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    private \M2E\TikTokShop\Model\Processing\Runner $processingRunner;
    private \M2E\TikTokShop\Model\Processing\Lock\Repository $lockRepository;
    private \M2E\TikTokShop\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Processing\Runner $processingRunner,
        \M2E\TikTokShop\Model\Processing\Lock\Repository $lockRepository,
        \M2E\TikTokShop\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->processingRunner = $processingRunner;
        $this->lockRepository = $lockRepository;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
    }

    /**
     * @param \M2E\TikTokShop\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $context->getSynchronizationLog()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION);

        // ----------------------------------------

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

                $context->getOperationHistory()->addText(
                    "Starting Account (Shop) '{$account->getTitle()} ({$shop->getShopId()})'",
                );
                $context->getOperationHistory()->addTimePoint(
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
                    $context->getOperationHistory()->addText(
                        "Error '{$account->getTitle()} ({$shop->getShopId()})'. Message: {$e->getMessage()}",
                    );
                }

                // ----------------------------------------

                $context->getOperationHistory()->saveTimePoint($timePointId);
            }
        }
    }
}
