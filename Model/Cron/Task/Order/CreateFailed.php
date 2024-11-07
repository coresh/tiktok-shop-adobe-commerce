<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class CreateFailed extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'order/create_failed';

    public const MAX_TRIES_TO_CREATE_ORDER = 3;

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    /** @var \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory */
    private CreatorFactory $orderCreatorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
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
        $this->accountRepository = $accountRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
                $tikTokShopOrders = $this->getOrders($account);
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
                    'magento_order_creation_failure' => \M2E\TikTokShop\Model\Order::MAGENTO_ORDER_CREATION_FAILED_NO,
                    'magento_order_creation_fails_count' => 0,
                    'magento_order_creation_latest_attempt_date' => null,
                ]);
                $order->save();
                continue;
            }

            $ordersCreator->createMagentoOrder($order);
        }
    }

    protected function getOrders(\M2E\TikTokShop\Model\Account $account)
    {
        $backToDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $backToDate->modify('-15 minutes');

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('account_id', $account->getId());
        $collection->addFieldToFilter('magento_order_id', ['null' => true]);
        $collection->addFieldToFilter(
            'magento_order_creation_failure',
            \M2E\TikTokShop\Model\Order::MAGENTO_ORDER_CREATION_FAILED_YES,
        );
        $collection->addFieldToFilter(
            'magento_order_creation_fails_count',
            ['lt' => self::MAX_TRIES_TO_CREATE_ORDER],
        );
        $collection->addFieldToFilter(
            'magento_order_creation_latest_attempt_date',
            ['lt' => $backToDate->format('Y-m-d H:i:s')],
        );
        $collection->getSelect()->order('magento_order_creation_latest_attempt_date ASC');
        $collection->setPageSize(25);

        return $collection->getItems();
    }
}
