<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector;

class Protocol implements \M2E\Core\Model\Connector\ProtocolInterface
{
    public const COMPONENT_NAME = 'TikTokShop';
    public const COMPONENT_VERSION = 16;

    public function getComponent(): string
    {
        return self::COMPONENT_NAME;
    }

    public function getComponentVersion(): int
    {
        return self::COMPONENT_VERSION;
    }
}
