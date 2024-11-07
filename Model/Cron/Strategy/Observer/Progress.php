<?php

namespace M2E\TikTokShop\Model\Cron\Strategy\Observer;

use Magento\Framework\Event\Observer;

class Progress implements \Magento\Framework\Event\ObserverInterface
{
    /** @var bool */
    private $isEnabled = false;

    /** @var \M2E\TikTokShop\Model\Lock\Item\Manager */
    private $lockItemManager = null;

    private \M2E\TikTokShop\Model\Lock\Item\ProgressFactory $lockItemProgressFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Lock\Item\ProgressFactory $lockItemProgressFactory
    ) {
        $this->lockItemProgressFactory = $lockItemProgressFactory;
    }

    public function enable()
    {
        $this->isEnabled = true;

        return $this;
    }

    public function disable()
    {
        $this->isEnabled = false;

        return $this;
    }

    public function setLockItemManager(\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager)
    {
        $this->lockItemManager = $lockItemManager;

        return $this;
    }

    public function execute(Observer $observer)
    {
        if (!$this->isEnabled) {
            return;
        }

        if ($this->lockItemManager === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Lock Item Manager was not set.');
        }

        $eventName = $observer->getEvent()->getName();
        $progressNick = $observer->getEvent()->getProgressNick();

        $progress = $this->lockItemProgressFactory->create(
            $this->lockItemManager,
            $progressNick
        );

        if ($eventName == \M2E\TikTokShop\Model\Cron\Strategy::PROGRESS_START_EVENT_NAME) {
            $progress->start();

            return;
        }

        if ($eventName == \M2E\TikTokShop\Model\Cron\Strategy::PROGRESS_SET_PERCENTAGE_EVENT_NAME) {
            $percentage = $observer->getEvent()->getData('percentage');
            $progress->setPercentage($percentage);

            return;
        }

        if ($eventName == \M2E\TikTokShop\Model\Cron\Strategy::PROGRESS_SET_DETAILS_EVENT_NAME) {
            $args = [
                'percentage' => $observer->getEvent()->getData('percentage'),
                'total' => $observer->getEvent()->getData('total'),
            ];
            $progress->setDetails($args);

            return;
        }

        if ($eventName == \M2E\TikTokShop\Model\Cron\Strategy::PROGRESS_STOP_EVENT_NAME) {
            $progress->stop();
        }
    }
}
