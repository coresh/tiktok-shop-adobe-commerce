<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Exception\Connection;

class UnableUpdateData extends \M2E\TikTokShop\Model\Exception\Connection
{
    private \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection;

    public function __construct(\M2E\TikTokShop\Model\Connector\Response\MessageCollection $messageCollection)
    {
        parent::__construct($messageCollection->getCombinedComponentErrorsString());

        $this->messageCollection = $messageCollection;
    }

    public function getMessageCollection(): \M2E\TikTokShop\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }
}
