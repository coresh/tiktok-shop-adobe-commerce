<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Shipping\GetProviders;

class ShippingProvider
{
    private string $id;
    private string $name;

    public function __construct(
        string $id,
        string $name
    ) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
