<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class CreateFailedTask extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'order/create_failed';

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    /** @var \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory */
    private CreatorFactory $orderCreatorFactory;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
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
        $this->orderRepository = $orderRepository;
        $this->accountRepository = $accountRepository;
        $this->orderCreatorFactory = $orderCreatorFactory;
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

    protected function performActions()
    {
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
            } catch (\Exception $exception) {
                $message = (string)\__(
                    'The "Create Failed Orders" Action for Account "%1" was completed with error.',
                    $account->getTitle(),
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    protected function createMagentoOrders($tikTokShopOrders)
    {
        $ordersCreator = $this->orderCreatorFactory->create($this->getSynchronizationLog());

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
