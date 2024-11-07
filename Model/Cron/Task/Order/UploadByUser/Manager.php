<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser;

class Manager
{
    private string $identifier;
    private \M2E\TikTokShop\Model\Registry\Manager $registryManager;

    public function __construct(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Registry\Manager $registryManager
    ) {
        $this->registryManager = $registryManager;
        $this->identifier = $account->getOpenId();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @throws \Exception
     */
    public function isEnabled(): bool
    {
        return $this->getFromDate() !== null
            && $this->getToDate() !== null;
    }

    /**
     * @throws \Exception
     */
    public function getFromDate(): ?\DateTime
    {
        $date = $this->getSettings('from_date');
        if (empty($date)) {
            return null;
        }

        return \M2E\TikTokShop\Helper\Date::createDateGmt($date);
    }

    /**
     * @throws \Exception
     */
    public function getToDate(): ?\DateTime
    {
        $date = $this->getSettings('to_date');
        if (empty($date)) {
            return null;
        }

        return \M2E\TikTokShop\Helper\Date::createDateGmt($date);
    }

    /**
     * @throws \Exception
     */
    public function getCurrentFromDate(): ?\DateTime
    {
        $date = $this->getSettings('current_from_date');
        if (empty($date)) {
            return null;
        }

        return \M2E\TikTokShop\Helper\Date::createDateGmt($date);
    }

    //----------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function setFromToDates(\DateTime $fromDate, \DateTime $toDate): void
    {
        $this->validate($fromDate, $toDate);

        $this->setSettings('from_date', $fromDate->format('Y-m-d H:i:s'));
        $this->setSettings('to_date', $toDate->format('Y-m-d H:i:s'));
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function validate(\DateTime $from, \DateTime $to): void
    {
        if ($from->getTimestamp() > $to->getTimestamp()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('From date is bigger than To date.');
        }

        $nowTimestamp = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->getTimestamp();
        if (
            $from->getTimestamp() > $nowTimestamp
            || $to->getTimestamp() > $nowTimestamp
        ) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Dates you provided are bigger than current.');
        }

        if ($from->diff($to)->days > 30) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('From - to interval provided is too big. (Max: 30 days)');
        }

        $minDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        $minDate->modify('-90 days');

        if ($from->getTimestamp() < $minDate->getTimestamp()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('From date provided is too old. (Max: 90 days)');
        }
    }

    public function setCurrentFromDate(string $currentFromDate): void
    {
        $this->setSettings('current_from_date', $currentFromDate);
    }

    //----------------------------------------

    public function clear(): void
    {
        $this->deleteSettings();
    }

    //########################################

    /**
     * @return array|bool|mixed|null
     */
    private function getSettings(?string $key = null)
    {
        $value = $this->registryManager->getValueFromJson($this->getRegistryKey());
        if ($key === null) {
            return $value;
        }

        return $value[$key] ?? null;
    }

    private function setSettings($key, $value): void
    {
        $settings = $this->registryManager->getValueFromJson($this->getRegistryKey());
        $settings[$key] = $value;

        $this->registryManager->setValue($this->getRegistryKey(), $settings);
    }

    private function deleteSettings(): void
    {
        $this->registryManager->deleteValue($this->getRegistryKey());
    }

    private function getRegistryKey(): string
    {
        return "/orders/upload_by_user/$this->identifier/";
    }
}
