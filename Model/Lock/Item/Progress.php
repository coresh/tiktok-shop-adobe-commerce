<?php

namespace M2E\TikTokShop\Model\Lock\Item;

class Progress
{
    public const CONTENT_DATA_KEY = 'progress_data';

    private Manager $lockItemManager;
    private string $progressNick;

    public function __construct(
        \M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager,
        $progressNick
    ) {
        $this->lockItemManager = $lockItemManager;
        $this->progressNick = str_replace('/', '-', $progressNick);
    }

    public function isInProgress()
    {
        $contentData = $this->lockItemManager->getContentData();

        return isset($contentData[self::CONTENT_DATA_KEY][$this->progressNick]);
    }

    // ---------------------------------------

    public function start()
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick] = ['percentage' => 0];

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    public function setPercentage($percentage)
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick]['percentage'] = $percentage;

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    public function setDetails($args = [])
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick] = $args;

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    public function stop()
    {
        $contentData = $this->lockItemManager->getContentData();

        if ($contentData === null) {
            $this->lockItemManager->setContentData([]);

            return $this;
        }

        unset($contentData[self::CONTENT_DATA_KEY][$this->progressNick]);

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }
}
