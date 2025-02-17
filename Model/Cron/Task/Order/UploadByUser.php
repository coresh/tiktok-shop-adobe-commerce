<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class UploadByUser implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/upload_by_user';

    private \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor;
    private \M2E\TikTokShop\Model\Synchronization\LogService $syncLog;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByCreateDate\Processor $receiveOrderProcessor,
        \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory,
        \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory
    ) {
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->uploadByUserManagerFactory = $uploadByUserManagerFactory;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
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

        $ordersCreator = $this->orderCreatorFactory->create($context->getSynchronizationLog());
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

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    private function processResponseMessages(
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ): void {
        foreach ($messageCollection->getMessages() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError()
                ? \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR
                : \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_WARNING;

            $this->syncLog
                ->add((string)__($message->getText()), $logType);
        }
    }
}
