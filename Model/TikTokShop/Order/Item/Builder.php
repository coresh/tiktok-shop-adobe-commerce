<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order\Item;

class Builder
{
    private bool $isNew = false;

    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Model\Order\ItemFactory $orderItemFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Order\Item\StatusResolver $statusResolver;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Model\Order\ItemFactory $orderItemFactory,
        \M2E\TikTokShop\Model\TikTokShop\Order\Item\StatusResolver $statusResolver
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->statusResolver = $statusResolver;
    }

    public function create(array $rawChannelData, int $orderId): \M2E\TikTokShop\Model\Order\Item
    {
        $orderItem = $this->findOrCreateOrderItem(
            $orderId,
            (string)$rawChannelData['id']
        );

        $orderItem->setItemStatus($this->statusResolver->resolve($rawChannelData['status']));

        $orderItem->setChannelProductId($rawChannelData['product_id'])
            ->setChannelSkuId($rawChannelData['sku_id'])
            ->setPackageId($rawChannelData['package_id'])
            ->setChannelProductTitle($rawChannelData['product_name'])
            ->setSku($rawChannelData['seller_sku']);

        // Price
        $orderItem->setSalePrice((float)$rawChannelData['sale_price'])
            ->setOriginalPrice((float)$rawChannelData['original_price'])
            ->setPlatformDiscount((float)$rawChannelData['platform_discount'])
            ->setSellerDiscount((float)$rawChannelData['seller_discount']);

        $orderItem->setCancelReason($this->getCancelReason($rawChannelData));
        $orderItem->setBuyerRequestReturn($this->getIsBuyerRequestReturn($rawChannelData));
        $orderItem->setBuyerRequestRefund($this->getIsBuyerRequestRefund($rawChannelData));
        $orderItem->setRefundReturnId($rawChannelData['refund_return_id'] ?? null);
        $orderItem->setRefundReturnStatus($rawChannelData['refund_return_status'] ?? null);

        $orderItem->setTaxDetails($this->getTaxDetails($rawChannelData));
        $orderItem->setTrackingDetails($this->getTrackingDetails($rawChannelData));
        $orderItem->setIsGift($rawChannelData['is_gift'] ?? false);

        if ($this->isNew) {
            $this->orderItemRepository->create($orderItem);
        } else {
            $this->orderItemRepository->save($orderItem);
        }

        return $orderItem;
    }

    private function findOrCreateOrderItem(int $orderId, string $ttsItemId): \M2E\TikTokShop\Model\Order\Item
    {
        $orderItem = $this->orderItemRepository->findByOrderIdAndItemId($orderId, $ttsItemId);

        if ($orderItem !== null) {
            $this->isNew = false;

            return $orderItem;
        }

        $this->isNew = true;

        return $this->orderItemFactory->create($orderId, $ttsItemId);
    }

    private function getTaxDetails(array $rawData): array
    {
        if (!array_key_exists('taxes', $rawData)) {
            return [];
        }

        $taxDetails = [];
        foreach ($rawData['taxes'] as $itemTax) {
            $taxDetails[] = [
                'type' => $itemTax['type'],
                'amount' => (float)$itemTax['amount'],
            ];
        }

        return $taxDetails;
    }

    private function getTrackingDetails(array $rawData): array
    {
        return [
            'shipping_provider_id' => $rawData['shipping_provider_id'] ?? '',
            'shipping_provider_name' => $rawData['shipping_provider_name'] ?? '',
            'tracking_number' => $rawData['tracking_number'] ?? '',
            'ship_date' => $rawData['rts_time'] ?? '',
        ];
    }

    private function getCancelReason(array $rawData): ?string
    {
        if (!array_key_exists('cancel_reason', $rawData)) {
            return null;
        }

        return (string)$rawData['cancel_reason'];
    }

    private function getIsBuyerRequestReturn(array $rawData): bool
    {
        if (!array_key_exists('is_buyer_request_return', $rawData)) {
            return false;
        }

        return (bool)$rawData['is_buyer_request_return'];
    }

    private function getIsBuyerRequestRefund(array $rawData): bool
    {
        if (!array_key_exists('is_buyer_request_refund', $rawData)) {
            return false;
        }

        return (bool)$rawData['is_buyer_request_refund'];
    }
}
