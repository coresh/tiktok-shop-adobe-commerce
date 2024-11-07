<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions;

class Response
{
    private array $deliveryOptions;
    private \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messagesCollection;

    /**
     * @param \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DeliveryOption[] $deliveryOptions
     * @param \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messagesCollection
     */
    public function __construct(
        array $deliveryOptions,
        \M2E\TikTokShop\Model\Connector\Response\MessageCollection $messagesCollection
    ) {
        $this->deliveryOptions = $deliveryOptions;
        $this->messagesCollection = $messagesCollection;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DeliveryOption[]
     */
    public function getDeliveryOptions(): array
    {
        return $this->deliveryOptions;
    }

    public function getMessagesCollection(): \M2E\TikTokShop\Model\Connector\Response\MessageCollection
    {
        return $this->messagesCollection;
    }
}
