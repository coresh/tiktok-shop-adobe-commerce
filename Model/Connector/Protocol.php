<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector;

class Protocol implements ProtocolInterface
{
    public const COMPONENT_VERSION = 8;

    public function getComponent(): string
    {
        return 'TikTokShop';
    }

    public function getComponentVersion(): int
    {
        return self::COMPONENT_VERSION;
    }
}
