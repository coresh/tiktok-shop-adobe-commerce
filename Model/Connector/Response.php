<?php

namespace M2E\TikTokShop\Model\Connector;

class Response
{
    private array $data;
    private string $resultType;
    private ?\DateTime $requestTime = null;
    private \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection;

    public function __construct(
        array $data,
        \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection,
        string $resultType
    ) {
        $this->data = $data;
        $this->messageCollection = $messageCollection;
        $this->resultType = $resultType;
    }

    public function isResultSuccess(): bool
    {
        return $this->resultType === \M2E\TikTokShop\Model\Response\Message::TYPE_SUCCESS;
    }

    public function isResultError(): bool
    {
        return $this->resultType === \M2E\TikTokShop\Model\Response\Message::TYPE_ERROR;
    }

    public function isResultWarning(): bool
    {
        return $this->resultType === \M2E\TikTokShop\Model\Response\Message::TYPE_WARNING;
    }

    public function isResultNotice(): bool
    {
        return $this->resultType === \M2E\TikTokShop\Model\Response\Message::TYPE_NOTICE;
    }

    public function getResultType(): string
    {
        return $this->resultType;
    }

    public function getMessageCollection(): \M2E\TikTokShop\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }

    public function getResponseData(): array
    {
        return $this->data;
    }

    public function setRequestTime(\DateTime $requestTime): void
    {
        $this->requestTime = $requestTime;
    }

    public function getRequestTime(): ?\DateTime
    {
        return $this->requestTime;
    }
}
