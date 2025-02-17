<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class SyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/sync';

    private Sync\OrdersProcessorFactory $ordersProcessorFactory;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        Sync\OrdersProcessorFactory $ordersProcessorFactory
    ) {
        $this->ordersProcessorFactory = $ordersProcessorFactory;
        $this->accountRepository = $accountRepository;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    /**
     * @param \M2E\TikTokShop\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            foreach ($account->getShops() as $shop) {
                try {
                    $ordersProcessor = $this->ordersProcessorFactory->create($shop, $context->getSynchronizationLog());
                    $ordersProcessor->process();
                } catch (\Throwable $e) {
                    $context->getExceptionHandler()->processTaskException($e);
                }
            }
        }
    }
}
