<?php

namespace M2E\TikTokShop\Model\Cron\Strategy\Observer;

use Magento\Framework\Event\Observer;

class KeepAlive implements \Magento\Framework\Event\ObserverInterface
{
    public const ACTIVATE_INTERVAL = 30;

    /** @var bool */
    private $isEnabled = false;

    /** @var \M2E\TikTokShop\Model\Lock\Item\Manager */
    private $lockItemManager = null;

    /** @var int|null */
    private $circleStartTime = null;

    public function enable(): self
    {
        $this->isEnabled = true;
        $this->circleStartTime = null;

        return $this;
    }

    public function disable(): self
    {
        $this->isEnabled = false;
        $this->circleStartTime = null;

        return $this;
    }

    // ----------------------------------------

    public function setLockItemManager(\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager): self
    {
        $this->lockItemManager = $lockItemManager;

        return $this;
    }

    // ----------------------------------------

    public function execute(Observer $observer)
    {
        if (!$this->isEnabled) {
            return;
        }

        if ($this->lockItemManager === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Lock Item Manager was not set.');
        }

        if (
            $observer->getEvent()->getData('object') &&
            ($observer->getEvent()->getData('object') instanceof \M2E\TikTokShop\Model\Lock\Item)
        ) {
            return;
        }

        if (
            $observer->getEvent()->getData('collection') &&
            (
                $observer->getEvent()->getData('collection') instanceof
                \M2E\TikTokShop\Model\ResourceModel\Lock\Item\Collection
            )
        ) {
            return;
        }

        if ($this->circleStartTime === null) {
            $this->circleStartTime = time();

            return;
        }

        if ($this->circleStartTime + self::ACTIVATE_INTERVAL > time()) {
            return;
        }

        $this->lockItemManager->activate();

        $this->circleStartTime = time();
    }
}
