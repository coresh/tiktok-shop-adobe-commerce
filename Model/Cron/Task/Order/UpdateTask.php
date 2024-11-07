<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class UpdateTask extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'order/update';

    private \M2E\TikTokShop\Model\Order\Change\Repository $orderChangeRepository;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Order\Change\ShippingProcessor $shippingProcessor;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Change\ShippingProcessor $shippingProcessor,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Order\Change\Repository $orderChangeRepository,
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
            $resource
        );
        $this->orderChangeRepository = $orderChangeRepository;
        $this->accountRepository = $accountRepository;
        $this->shippingProcessor = $shippingProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\TikTokShop\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $this->deleteNotActualChanges();

        $accounts = $this->accountRepository->getAll();
        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {
            $this->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->shippingProcessor->process($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Update" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    // ----------------------------------------

    private function deleteNotActualChanges(): void
    {
        $this->orderChangeRepository->deleteByProcessingAttemptCount(
            \M2E\TikTokShop\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS
        );
    }
}
