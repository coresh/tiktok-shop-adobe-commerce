<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class UploadByUser extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'order/upload_by_user';

    private \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor,
        \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory,
        \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory,
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
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->uploadByUserManagerFactory = $uploadByUserManagerFactory;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
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
        $ordersCreator = $this->orderCreatorFactory->create($this->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($this->accountRepository->getAll() as $account) {
            $manager = $this->uploadByUserManagerFactory->create($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $maxDate = null;
                $isNeedClearManager = true;
                foreach ($account->getShops() as $shop) {
                    $toTime = $manager->getToDate();
                    $fromTime = $manager->getCurrentFromDate();
                    if ($fromTime === null) {
                        $fromTime = $manager->getFromDate();
                    }

                    $response = $this->receiveOrderProcessor->process(
                        $account,
                        $shop,
                        $fromTime,
                        $toTime,
                    );

                    $this->processResponseMessages($response->getMessageCollection());

                    $isNeedClearManager = !$response->isHasMore();

                    if (empty($response->getOrders())) {
                        continue;
                    }

                    $processTikTokOrders = $ordersCreator
                        ->processTikTokOrders($shop, $response->getOrders());
                    $ordersCreator->processMagentoOrders($processTikTokOrders);

                    $responseMaxDate = \M2E\TikTokShop\Helper\Date::createDateGmt($response->getMaxDateInResult());
                    if (
                        $maxDate === null
                        || $maxDate->getTimestamp() < $responseMaxDate->getTimestamp()
                    ) {
                        $maxDate = $responseMaxDate;
                    }
                }

                if ($maxDate === null) {
                    $maxDate = $manager->getToDate();
                }
                $manager->setCurrentFromDate($maxDate->format('Y-m-d H:i:s'));

                if (
                    $isNeedClearManager
                    || $manager->getCurrentFromDate()->getTimestamp() >= $manager->getToDate()->getTimestamp()
                ) {
                    $manager->clear();
                }
            } catch (\Throwable $exception) {
                $message = (string)\__(
                    'The "Upload Orders By User" Action for TikTok Shop Account "%account" was completed with error.',
                    ['account' => $account->getTitle()],
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    private function processResponseMessages(
        \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection
    ): void {
        foreach ($messageCollection->getMessages() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError()
                ? \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR
                : \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_WARNING;

            $this
                ->getSynchronizationLog()
                ->add((string)__($message->getText()), $logType);
        }
    }
}
