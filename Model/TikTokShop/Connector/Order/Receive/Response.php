<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive;

class Response
{
    private array $orders;
    private \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection;
    private string $maxDateInResult;
    private bool $hasMore;

    public function __construct(
        array $orders,
        string $maxDateInResult,
        bool $hasMore,
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ) {
        $this->orders = $orders;
        $this->messageCollection = $messageCollection;
        $this->maxDateInResult = $maxDateInResult;
        $this->hasMore = $hasMore;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getMessageCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }

    public function getMaxDateInResult(): string
    {
        return $this->maxDateInResult;
    }

    public function isHasMore(): bool
    {
        return $this->hasMore;
    }
}
