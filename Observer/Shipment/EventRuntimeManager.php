<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\Shipment;

class EventRuntimeManager
{
    private static bool $isNeedSkipEvents = false;
    private static array $processedShipments = [];

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

    public function markShipmentAsProcessed(\Magento\Sales\Model\Order\Shipment $shipment): void
    {
        self::$processedShipments[$shipment->getId()] = true;
    }

    public function isShipmentProcessed(\Magento\Sales\Model\Order\Shipment $shipment): bool
    {
        return array_key_exists($shipment->getId(), self::$processedShipments);
    }
}
