<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\DeclineReasons\Command;

class Response
{
    private array $reasons;
    private \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection;

    /**
     * @param \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReason[] $reasons
     * @param \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection
     */
    public function __construct(
        array $reasons,
        \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection
    ) {
        $this->reasons = $reasons;
        $this->messagesCollection = $messagesCollection;
    }

    /**
     * @return \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReason[]
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function getMessagesCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messagesCollection;
    }
}
