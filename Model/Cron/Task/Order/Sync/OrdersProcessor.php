<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order\Sync;

use M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByUpdateDate\Processor as ItemsByUpdateDateProcessor;

class OrdersProcessor
{
    private \M2E\TikTokShop\Model\Synchronization\LogService $synchronizationLog;
    private \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory;
    private ItemsByUpdateDateProcessor $receiveOrdersProcessor;
    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Synchronization\LogService $logService,
        ItemsByUpdateDateProcessor $receiveOrdersProcessor,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Cron\Task\Order\CreatorFactory $orderCreatorFactory
    ) {
        $this->orderCreatorFactory = $orderCreatorFactory;
        $this->receiveOrdersProcessor = $receiveOrdersProcessor;
        $this->synchronizationLog = $logService;
        $this->shop = $shop;
        $this->shopRepository = $shopRepository;
    }

    public function process(): void
    {
        $toTime = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        $fromTime = $this->prepareFromTime($this->shop, $toTime);

        $response = $this->receiveOrdersProcessor->process(
            $this->shop->getAccount(),
            $this->shop,
            $fromTime,
            $toTime
        );

        $this->processResponseMessages($response->getMessageCollection());

        if (empty($response->getOrders())) {
            return;
        }

        $ordersCreator = $this->orderCreatorFactory->create($this->synchronizationLog);

        $processedTikTokOrders = $ordersCreator->processTikTokOrders($this->shop, $response->getOrders());
        $ordersCreator->processMagentoOrders($processedTikTokOrders);

        $this->updateLastOrderSynchronizationDate($this->shop, $response->getMaxDateInResult());
    }

    // ---------------------------------------

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

            $this->synchronizationLog->add((string)__($message->getText()), $logType);
        }
    }

    private function prepareFromTime(
        \M2E\TikTokShop\Model\Shop $shop,
        \DateTime $toTime
    ): \DateTime {
        $lastSynchronizationDate = $shop->getOrdersLastSyncDate();

        if ($lastSynchronizationDate === null) {
            $sinceTime = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        } else {
            $sinceTime = $lastSynchronizationDate;

            // Get min date for sync
            // ---------------------------------------
            $minDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
            $minDate->modify('-90 days');
            // ---------------------------------------

            // Prepare last date
            // ---------------------------------------
            if ($sinceTime->getTimestamp() < $minDate->getTimestamp()) {
                $sinceTime = $minDate;
            }
        }

        if ($sinceTime->getTimestamp() >= $toTime->getTimeStamp()) {
            $sinceTime = clone $toTime;
            $sinceTime->modify('- 5 minutes');
        }

        return $sinceTime;
    }

    private function updateLastOrderSynchronizationDate(
        \M2E\TikTokShop\Model\Shop $shop,
        string $date
    ): void {
        $shop->setOrdersLastSyncDate(\M2E\TikTokShop\Helper\Date::createDateGmt($date));

        $this->shopRepository->save($shop);
    }
}
