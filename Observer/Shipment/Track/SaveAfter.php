<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\Shipment\Track;

class SaveAfter extends \M2E\TikTokShop\Observer\AbstractObserver
{
    private \M2E\TikTokShop\Observer\Shipment\EventRuntimeManager $eventRuntimeManager;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\Order\ShipmentService $shipmentHandler;

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\Observer\Shipment\EventRuntimeManager $eventRuntimeManager,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\Order\ShipmentService $shipmentHandler
    ) {
        parent::__construct($helperFactory);
        $this->eventRuntimeManager = $eventRuntimeManager;
        $this->orderRepository = $orderRepository;
        $this->shipmentHandler = $shipmentHandler;
    }

    protected function process(): void
    {
        if ($this->eventRuntimeManager->isNeedSkipEvents()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $this->getEvent()->getTrack();
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $track->getShipment();

        if ($this->eventRuntimeManager->isShipmentProcessed($shipment)) {
            return;
        }

        $magentoOrderId = (int)$shipment->getOrderId();

        try {
            $order = $this->orderRepository->findByMagentoOrderId($magentoOrderId);
        } catch (\Throwable $e) {
            return;
        }

        if ($order === null) {
            return;
        }

        $this->shipmentHandler->shipByShipment($order, $shipment, \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION);

        $this->eventRuntimeManager->skipEvents();
    }
}
