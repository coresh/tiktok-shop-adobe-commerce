<?php

namespace M2E\TikTokShop\Helper\Magento;

class Carriers
{
    private \Magento\Shipping\Model\Config $shippingConfig;

    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @return \Magento\Shipping\Model\Carrier\AbstractCarrierInterface[]
     */
    public function getAllCarriers(): array
    {
        return $this->shippingConfig->getAllCarriers();
    }

    /**
     * @return \Magento\Shipping\Model\Carrier\AbstractCarrierInterface[]
     */
    public function getCarriersWithAvailableTracking(): array
    {
        $carriers = [];
        foreach ($this->getAllCarriers() as $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[] = $carrier;
            }
        }

        return $carriers;
    }
}
