<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct\VariantSku;

class SalesAttribute implements \JsonSerializable
{
    private string $name;
    private string $valueName;

    public function __construct(
        string $name,
        string $valueName
    ) {
        $this->name = $name;
        $this->valueName = $valueName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValueName(): string
    {
        return $this->valueName;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'value_name' => $this->valueName,
        ];
    }
}
