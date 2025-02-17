<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock\Item;

class Progress
{
    public const CONTENT_DATA_KEY = 'progress_data';

    private Manager $lockItemManager;
    private string $progressNick;

    public function __construct(
        \M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager,
        string $progressNick
    ) {
        $this->lockItemManager = $lockItemManager;
        $this->progressNick = str_replace('/', '-', $progressNick);
    }

    public function isInProgress(): bool
    {
        $contentData = $this->lockItemManager->getContentData();

        return isset($contentData[self::CONTENT_DATA_KEY][$this->progressNick]);
    }

    // ---------------------------------------

    public function start(): void
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick] = ['percentage' => 0];

        $this->lockItemManager->setContentData($contentData);
    }

    public function setPercentage($percentage): void
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick]['percentage'] = $percentage;

        $this->lockItemManager->setContentData($contentData);
    }

    public function setDetails(array $args): void
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick] = $args;

        $this->lockItemManager->setContentData($contentData);
    }

    public function stop(): void
    {
        $contentData = $this->lockItemManager->getContentData();

        unset($contentData[self::CONTENT_DATA_KEY][$this->progressNick]);

        $this->lockItemManager->setContentData($contentData);
    }
}
