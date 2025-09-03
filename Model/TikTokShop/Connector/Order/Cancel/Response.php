<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel;

class Response
{
    private bool $isRefund;
    private \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection;

    /**
     * @param bool $isRefund
     * @param \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection
     */
    public function __construct(
        bool $isRefund,
        \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection
    ) {
        $this->isRefund = $isRefund;
        $this->messagesCollection = $messagesCollection;
    }

    public function isRefund(): bool
    {
        return $this->isRefund;
    }

    public function getMessagesCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messagesCollection;
    }
}
