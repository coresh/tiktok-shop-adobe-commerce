<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest;

class Response
{
    private \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection;
    private ?Order $order;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection,
        ?Order $order
    ) {
        $this->messageCollection = $messageCollection;
        $this->order = $order;
    }

    public function hasErrors(): bool
    {
        return $this->messageCollection->hasErrors();
    }

    /**
     * @return \M2E\TikTokShop\Model\Connector\Response\Message[]
     */
    public function getErrorMessages(): array
    {
        return $this->messageCollection->getErrors();
    }

    public function hasOrder(): bool
    {
        return $this->order !== null;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
