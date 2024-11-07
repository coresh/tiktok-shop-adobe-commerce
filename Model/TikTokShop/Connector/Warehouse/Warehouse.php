<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse;

class Warehouse
{
    public const TYPE_SALES_WAREHOUSE = 'SALES_WAREHOUSE';
    public const TYPE_RETURN_WAREHOUSE = 'RETURN_WAREHOUSE';

    public const SUB_TYPE_DOMESTIC_WAREHOUSE = 'DOMESTIC_WAREHOUSE';
    public const SUB_TYPE_CB_OVERSEA_WAREHOUSE = 'CB_OVERSEA_WAREHOUSE';
    public const SUB_TYPE_CB_DIRECT_SHIPPING_WAREHOUSE = 'CB_DIRECT_SHIPPING_WAREHOUSE';

    public const EFFECT_STATUS_ENABLED = 'ENABLED';
    public const EFFECT_STATUS_DISABLED = 'DISABLED';
    public const EFFECT_STATUS_RESTRICTED = 'RESTRICTED';
    public const EFFECT_STATUS_HOLIDAY_MODE = 'Holiday mode';
    public const EFFECT_STATUS_ORDER_LIMIT_MODE = 'Order limit mode';

    private string $id;
    private string $name;
    private string $effectStatus;
    private string $type;
    private string $subType;
    private bool $isDefault;
    private array $address;

    public function __construct(
        string $id,
        string $name,
        string $effectStatus,
        string $type,
        string $subType,
        bool $isDefault,
        array $address
    ) {
        $this->validateType($type);
        $this->validateSubType($subType);
        $this->validateEffectStatus($effectStatus);

        $this->id = $id;
        $this->name = $name;
        $this->effectStatus = $effectStatus;
        $this->type = $type;
        $this->subType = $subType;
        $this->isDefault = $isDefault;
        $this->address = $address;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEffectStatus(): string
    {
        return $this->effectStatus;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubType(): string
    {
        return $this->subType;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getAddress(): array
    {
        return $this->address;
    }

    private function validateType(string $type): void
    {
        if (
            !in_array(
                $type,
                [
                    self::TYPE_SALES_WAREHOUSE,
                    self::TYPE_RETURN_WAREHOUSE,
                ],
            )
        ) {
            throw new \LogicException("Warehouse type '$type' no valid.");
        }
    }

    private function validateSubType(string $subType): void
    {
        if (
            !in_array(
                $subType,
                [
                    self::SUB_TYPE_DOMESTIC_WAREHOUSE,
                    self::SUB_TYPE_CB_OVERSEA_WAREHOUSE,
                    self::SUB_TYPE_CB_DIRECT_SHIPPING_WAREHOUSE,
                ],
            )
        ) {
            throw new \LogicException("Warehouse sub type '$subType' no valid.");
        }
    }

    private function validateEffectStatus(string $effectStatus): void
    {
        if (
            !in_array(
                $effectStatus,
                [
                    self::EFFECT_STATUS_ENABLED,
                    self::EFFECT_STATUS_DISABLED,
                    self::EFFECT_STATUS_RESTRICTED,
                    self::EFFECT_STATUS_HOLIDAY_MODE,
                    self::EFFECT_STATUS_ORDER_LIMIT_MODE,
                ],
            )
        ) {
            throw new \LogicException("Warehouse effect status '$effectStatus' no valid.");
        }
    }
}
