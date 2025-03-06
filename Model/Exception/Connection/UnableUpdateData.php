<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Exception\Connection;

class UnableUpdateData extends \M2E\Core\Model\Exception\Connection
{
    private \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection;

    public function __construct(\M2E\Core\Model\Connector\Response\MessageCollection $messageCollection)
    {
        $messages = [];
        foreach ($messageCollection->getErrors() as $message) {
            if (!$message->isSenderComponent()) {
                continue;
            }
            $messages[] = $message->getText();
        }

        $combinedMessages = !empty($messages) ? implode(', ', $messages) : '';

        parent::__construct($combinedMessages);

        $this->messageCollection = $messageCollection;
    }

    public function getMessageCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }
}
