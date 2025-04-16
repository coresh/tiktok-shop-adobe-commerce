<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class ReserveCancelTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/reserve_cancel';

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param \M2E\TikTokShop\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_ORDERS);
        $context->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        $permittedAccounts = $this->accountRepository->getAll();

        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            $context->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->processAccount($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Reserve Cancellation" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    private function processAccount(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($this->getOrdersForRelease($account) as $order) {
            /** @var \M2E\TikTokShop\Model\Order $order */
            $order->getReserve()->release();
        }
    }

    private function getOrdersForRelease(\M2E\TikTokShop\Model\Account $account): array
    {
        $collection = $this->orderCollectionFactory->create()
                                          ->addFieldToFilter('account_id', $account->getId())
                                          ->addFieldToFilter(
                                              'reservation_state',
                                              \M2E\TikTokShop\Model\Order\Reserve::STATE_PLACED
                                          );

        $reservationDays = $account->getOrdersSettings()->getQtyReservationDays();

        $minReservationStartDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $minReservationStartDate->modify('- ' . $reservationDays . ' days');
        $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

        $collection->addFieldToFilter('reservation_start_date', ['lteq' => $minReservationStartDate]);

        return $collection->getItems();
    }
}
