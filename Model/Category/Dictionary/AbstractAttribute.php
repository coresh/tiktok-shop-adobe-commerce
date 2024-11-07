<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

abstract class AbstractAttribute
{
    private string $id;
    private string $name;
    private bool $isRequired;
    private bool $isCustomised;
    private bool $isMultipleSelected;
    /** @var \M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value[] */
    private array $recommendedValuers;

    public function __construct(
        string $id,
        string $name,
        bool $isRequired,
        bool $isCustomised,
        bool $isMultipleSelected,
        array $recommendedValuers = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->isCustomised = $isCustomised;
        $this->isMultipleSelected = $isMultipleSelected;
        $this->recommendedValuers = $recommendedValuers;
    }

    abstract public function getType(): string;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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
     * @return array|\M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value[]
     */
    public function getValues(): array
    {
        return $this->recommendedValuers;
    }
}
