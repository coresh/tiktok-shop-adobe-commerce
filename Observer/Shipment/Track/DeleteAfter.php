<?php

namespace M2E\TikTokShop\Observer\Shipment\Track;

class DeleteAfter extends \M2E\TikTokShop\Observer\AbstractObserver
{
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\Order\Change\Repository $orderChangeRepository;
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\Order\Change\Repository $orderChangeRepository,
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderChangeRepository = $orderChangeRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $this->getEvent()->getTrack();
        $shipment = $track->getShipment();
        if ($shipment === null) {
            return;
        }

        $magentoOrderId = (int)$shipment->getOrderId();

        try {
            $order = $this->orderRepository->findByMagentoOrderId($magentoOrderId);
        } catch (\Throwable $throwable) {
            return;
        }

        if ($order === null) {
            return;
        }

        $existedOrderChanges = $this->orderChangeRepository->findShippingNotStarted($order->getId());
        foreach ($existedOrderChanges as $existedOrderChange) {
            $orderChangeParams = $existedOrderChange->getParams();

            if (
                !isset($orderChangeParams['magento_shipment_id'])
                || $orderChangeParams['magento_shipment_id'] != $shipment->getId()
            ) {
                continue;
            }

            if ($orderChangeParams['tracking_number'] !== $track->getTrackNumber()) {
                continue;
            }

            $orderItems = $this->orderItemRepository->getByIds(
                array_column($orderChangeParams['items'], 'item_id')
            );

            foreach ($orderItems as $orderItem) {
                $orderItem->setShippingInProgressNo();
                $this->orderItemRepository->save($orderItem);
            }

            $this->orderChangeRepository->delete($existedOrderChange);
        }
    }
}
