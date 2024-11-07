<?php

namespace M2E\TikTokShop\Model\Connector;

interface ProtocolInterface
{
    /**
     * @return string
     */
    public function getComponent(): string;

    /**
     * @return int
     */
    public function getComponentVersion(): int;
}
