<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Shipment;

class TrackingDetailsBuilder
{
    private \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory;

    public function __construct(
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
    ) {
        $this->carrierFactory = $carrierFactory;
    }

    public function build(
        \Magento\Sales\Model\Order\Shipment $shipment,
        int $storeId
    ): ?\M2E\TikTokShop\Model\Order\Shipment\Data\TrackingDetails {
        $track = $this->getLastTrack($shipment);
        if ($track === null) {
            return null;
        }

        $trackNumber = $this->getTrackNumber($track);
        if (empty($trackNumber)) {
            return null;
        }

        return new \M2E\TikTokShop\Model\Order\Shipment\Data\TrackingDetails(
            (int)$shipment->getId(),
            $this->getTrackCarrierCode($track),
            $this->getTrackCarrierTitle($track, $storeId),
            $this->getShippingMethod($track),
            $trackNumber
        );
    }

    private function getLastTrack(
        \Magento\Sales\Model\Order\Shipment $shipment
    ): ?\Magento\Sales\Model\Order\Shipment\Track {
        $tracks = $shipment->getTracks();
        if (empty($tracks)) {
            $tracks = $shipment->getTracksCollection()->getItems();
        }

        if (empty($tracks)) {
            return null;
        }

        return end($tracks);
    }

    private function getTrackNumber(\Magento\Sales\Model\Order\Shipment\Track $track): string
    {
        $trackNumber = $track->getNumber();
        return trim((string)$trackNumber);
    }

    private function getTrackCarrierCode(\Magento\Sales\Model\Order\Shipment\Track $track): string
    {
        return trim((string)$track->getCarrierCode());
    }

    private function getTrackCarrierTitle(
        \Magento\Sales\Model\Order\Shipment\Track $track,
        int $storeId
    ): string {
        $carrierCode = $this->getTrackCarrierCode($track);
        $carrier = $this->carrierFactory->create($carrierCode, $storeId);

        if ($carrier) {
            return trim((string)$carrier->getConfigData('title'));
        }

        return $carrierCode;
    }

    private function getShippingMethod(\Magento\Sales\Model\Order\Shipment\Track $track): string
    {
        return trim((string)$track->getTitle());
    }
}
