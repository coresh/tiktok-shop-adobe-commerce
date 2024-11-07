<?php

namespace M2E\TikTokShop\Model\Magento\Quote\Store;

class ConfiguratorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \Magento\Quote\Model\Quote $quote,
        \M2E\TikTokShop\Model\Order\ProxyObject $proxyOrder,
        array $data = []
    ): Configurator {
        $data['quote'] = $quote;
        $data['proxyOrder'] = $proxyOrder;

        return $this->objectManager->create(Configurator::class, $data);
    }
}
