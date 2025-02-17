<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class CreateFailedTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/create_failed';

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    /** @var \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory */
    private CreatorFactory $orderCreatorFactory;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\Synchronization\LogService $syncLog;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->accountRepository = $accountRepository;
        $this->orderCreatorFactory = $orderCreatorFactory;
    }

    /**
     * @param \M2E\TikTokShop\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $this->syncLog = $context->getSynchronizationLog();
        $this->syncLog->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            try {
                $borderDate = new \DateTime('now', new \DateTimeZone('UTC'));
                $borderDate->modify('-15 minutes');

                $tikTokShopOrders = $this->orderRepository->findForAttemptMagentoCreate(
                    $account,
                    $borderDate,
                    \M2E\TikTokShop\Model\Order::MAGENTO_ORDER_CREATE_MAX_TRIES
                );
                $this->createMagentoOrders($tikTokShopOrders);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Create Failed Orders" Action for Account "%1" was completed with error.',
                    $account->getTitle(),
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    private function createMagentoOrders(array $tikTokShopOrders): void
    {
        $ordersCreator = $this->orderCreatorFactory->create($this->syncLog);

        foreach ($tikTokShopOrders as $order) {
            /** @var \M2E\TikTokShop\Model\Order $order */

            if ($ordersCreator->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            if (!$order->canCreateMagentoOrder()) {
                $order->addData([
                    \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_FAILURE => \M2E\TikTokShop\Model\Order::MAGENTO_ORDER_CREATION_FAILED_NO,
                    \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT => 0,
                    \M2E\TikTokShop\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE => null,
                ]);
                $this->orderRepository->save($order);
                continue;
            }

            $ordersCreator->createMagentoOrder($order);
        }
    }
}
