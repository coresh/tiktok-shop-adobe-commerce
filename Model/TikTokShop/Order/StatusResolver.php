<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order;

class StatusResolver
{
    private const ORDER_STATUS_UNPAID = 'unpaid';
    private const ORDER_STATUS_ON_HOLD = 'on_hold';
    private const ORDER_STATUS_PARTIALLY_SHIPPING = 'partially_shipping';
    private const ORDER_STATUS_AWAITING_SHIPMENT = 'awaiting_shipment';
    private const ORDER_STATUS_AWAITING_COLLECTION = 'awaiting_collection';
    private const ORDER_STATUS_IN_TRANSIT = 'in_transit';
    private const ORDER_STATUS_DELIVERED = 'delivered';
    private const ORDER_STATUS_COMPLETED = 'completed';
    private const ORDER_STATUS_CANCELLED = 'cancelled';

    public function resolve(string $tikTokOrderStatus): int
    {
        $tikTokOrderStatus = mb_strtolower($tikTokOrderStatus);

        if (
            $tikTokOrderStatus === self::ORDER_STATUS_UNPAID
            || $tikTokOrderStatus === self::ORDER_STATUS_ON_HOLD
        ) {
            return \M2E\TikTokShop\Model\Order::STATUS_PENDING;
        }

        if (
            $tikTokOrderStatus === self::ORDER_STATUS_PARTIALLY_SHIPPING
            || $tikTokOrderStatus === self::ORDER_STATUS_AWAITING_SHIPMENT
        ) {
            return \M2E\TikTokShop\Model\Order::STATUS_UNSHIPPED;
        }

        if (
            $tikTokOrderStatus === self::ORDER_STATUS_AWAITING_COLLECTION
            || $tikTokOrderStatus === self::ORDER_STATUS_IN_TRANSIT
            || $tikTokOrderStatus === self::ORDER_STATUS_DELIVERED
            || $tikTokOrderStatus === self::ORDER_STATUS_COMPLETED
        ) {
            return \M2E\TikTokShop\Model\Order::STATUS_SHIPPED;
        }

        if ($tikTokOrderStatus === self::ORDER_STATUS_CANCELLED) {
            return \M2E\TikTokShop\Model\Order::STATUS_CANCELED;
        }

        return \M2E\TikTokShop\Model\Order::STATUS_UNKNOWN;
    }
}
