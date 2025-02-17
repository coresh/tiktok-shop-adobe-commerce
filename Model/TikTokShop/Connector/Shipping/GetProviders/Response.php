<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders;

class Response
{
    private array $shippingProviders;
    private \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection;

    /**
     * @param \M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders\ShippingProvider[] $shippingProviders
     * @param \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection
     */
    public function __construct(
        array $shippingProviders,
        \M2E\Core\Model\Connector\Response\MessageCollection $messagesCollection
    ) {
        $this->shippingProviders = $shippingProviders;
        $this->messagesCollection = $messagesCollection;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders\ShippingProvider[]
     */
    public function getShippingProviders(): array
    {
        return $this->shippingProviders;
    }

    public function getMessagesCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messagesCollection;
    }
}
