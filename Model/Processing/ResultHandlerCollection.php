<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing;

class ResultHandlerCollection
{
    private const MAP = [
        \M2E\TikTokShop\Model\Listing\InventorySync\Processing\ResultHandler::NICK =>
            \M2E\TikTokShop\Model\Listing\InventorySync\Processing\ResultHandler::class,
    ];

    public function has(string $nick): bool
    {
        return isset(self::MAP[$nick]);
    }

    /**
     * @param string $nick
     *
     * @return string result handler class name
     */
    public function get(string $nick): string
    {
        return self::MAP[$nick];
    }
}
