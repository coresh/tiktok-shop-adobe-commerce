<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\Item;

class StatusResolver
{
    private const TTS_STATUS_UNPAID = 'unpaid';
    private const TTS_STATUS_AWAITING_SHIPMENT = 'awaiting_shipment';
    private const TTS_STATUS_AWAITING_COLLECTION = 'awaiting_collection';
    private const TTS_STATUS_IN_TRANSIT = 'in_transit';
    private const TTS_STATUS_DELIVERED = 'delivered';
    private const TTS_STATUS_COMPLETED = 'completed';
    private const TTS_STATUS_CANCELLED = 'cancelled';
    private const TTS_STATUS_UNKNOWN = 'unknown';

    public function resolve(string $ttsOrderItemStatus): int
    {
        $ttsOrderItemStatus = mb_strtolower($ttsOrderItemStatus);

        if ($ttsOrderItemStatus === self::TTS_STATUS_UNPAID) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_UNPAID;
        }

        if ($ttsOrderItemStatus === self::TTS_STATUS_AWAITING_SHIPMENT) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_AWAITING_SHIPMENT;
        }

        if ($ttsOrderItemStatus === self::TTS_STATUS_AWAITING_COLLECTION) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_AWAITING_COLLECTION;
        }

        if ($ttsOrderItemStatus === self::TTS_STATUS_IN_TRANSIT) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_IN_TRANSIT;
        }

        if ($ttsOrderItemStatus === self::TTS_STATUS_DELIVERED) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_DELIVERED;
        }

        if ($ttsOrderItemStatus === self::TTS_STATUS_COMPLETED) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_COMPLETED;
        }

        if ($ttsOrderItemStatus === self::TTS_STATUS_CANCELLED) {
            return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_CANCELLED;
        }

        return \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_UNKNOWN;
    }
}
