<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\Shipment;

class EventRuntimeManager
{
    private static bool $isNeedSkipEvents = false;
    private static bool $isNeedSkipShippingHandler = false;

    public function skipEvents(): void
    {
        self::$isNeedSkipEvents = true;
    }

    public function doNotSkipEvents(): void
    {
        self::$isNeedSkipEvents = false;
    }

    public function isNeedSkipEvents(): bool
    {
        return self::$isNeedSkipEvents;
    }

    public function skipShippingHandler(): void
    {
        self::$isNeedSkipShippingHandler = true;
    }

    public function doNotSkipShippingHandler(): void
    {
        self::$isNeedSkipShippingHandler = false;
    }

    public function isNeedSkipShippingHandler(): bool
    {
        return self::$isNeedSkipShippingHandler;
    }
}
