<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Attribute;

class Attribute
{
    public const SALES_TYPE = 'SALES_PROPERTY';
    public const PRODUCT_TYPE = 'PRODUCT_PROPERTY';

    private string $id;
    private string $name;
    private string $type;
    private bool $isRequired;
    private bool $isCustomised;
    private bool $isMultipleSelected;
    private array $values = [];

    public function __construct(
        string $id,
        string $name,
        string $type,
        bool $isRequired,
        bool $isCustomised,
        bool $isMultipleSelected
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->isRequired = $isRequired;
        $this->isCustomised = $isCustomised;
        $this->isMultipleSelected = $isMultipleSelected;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSalesType(): bool
    {
        return $this->type === self::SALES_TYPE;
    }

    public function isProductType(): bool
    {
        return $this->type === self::PRODUCT_TYPE;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isCustomised(): bool
    {
        return $this->isCustomised;
    }

    public function isMultipleSelected(): bool
    {
        return $this->isMultipleSelected;
    }

    /**
     * @return list<array{id:string, name:string}>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function addValue(string $id, string $name): void
    {
        $this->values[] = [
            'id' => $id,
            'name' => $name,
        ];
    }
}
