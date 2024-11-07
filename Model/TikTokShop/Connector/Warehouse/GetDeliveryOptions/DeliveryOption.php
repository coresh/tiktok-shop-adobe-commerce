<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions;

class DeliveryOption
{
    private string $id;
    private string $name;
    private string $type;
    private string $description;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DimensionLimit $dimensionLimit;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\WeightLimit $weightLimit;

    public function __construct(
        string $id,
        string $name,
        string $type,
        string $description,
        DimensionLimit $dimensionLimit,
        WeightLimit $weightLimit
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->dimensionLimit = $dimensionLimit;
        $this->weightLimit = $weightLimit;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\DimensionLimit
     */
    public function getDimensionLimit(): DimensionLimit
    {
        return $this->dimensionLimit;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetDeliveryOptions\WeightLimit
     */
    public function getWeightLimit(): WeightLimit
    {
        return $this->weightLimit;
    }
}
