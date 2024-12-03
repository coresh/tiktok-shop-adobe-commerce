<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Product;

use M2E\TikTokShop\Model\Cron\Task\Order\Sync;

class PromotionSync extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'promotion/sync';

    /** @var int in seconds */
    protected int $intervalInSeconds = 28800;

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Promotion\Synchronization $promotionsSynchronization;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Promotion\Synchronization $promotionsSynchronization,
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
        $this->promotionsSynchronization = $promotionsSynchronization;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\TikTokShop\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_OTHER);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $permittedAccounts = $this->accountRepository->getAll();

        if (empty($permittedAccounts)) {
            return;
        }

        $this->getSynchronizationLog()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION);

        foreach ($permittedAccounts as $account) {
            foreach ($account->getShops() as $shop) {
                try {
                    $this->promotionsSynchronization->process($account, $shop);
                } catch (\Throwable $exception) {
                    $message = (string)__(
                        'The "Synchronize Promotions" Action for Account "%1" was completed with error.',
                        $account->getTitle()
                    );

                    $this->processTaskAccountException($message, __FILE__, __LINE__);
                    $this->processTaskException($exception);
                }
            }
        }
    }
}
