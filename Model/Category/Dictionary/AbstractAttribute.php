<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

abstract class AbstractAttribute
{
    protected string $id;
    private string $name;
    private bool $isRequired;
    private bool $isCustomised;
    private bool $isMultipleSelected;
    /** @var \M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value[] */
    private array $recommendedValuers;
    private string $sampleImageUrl;

    public function __construct(
        string $id,
        string $name,
        bool $isRequired,
        bool $isCustomised,
        bool $isMultipleSelected,
        array $recommendedValuers = [],
        string $sampleImageUrl = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->isCustomised = $isCustomised;
        $this->isMultipleSelected = $isMultipleSelected;
        $this->recommendedValuers = $recommendedValuers;
        $this->sampleImageUrl = $sampleImageUrl;
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

    public function getSampleImageUrl(): string
    {
        return $this->sampleImageUrl;
    }
}
