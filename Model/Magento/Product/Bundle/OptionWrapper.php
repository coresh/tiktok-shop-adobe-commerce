<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Bundle;

class OptionWrapper
{
    private const OPTION_TYPE_SELECT = 'select';
    private const OPTION_TYPE_RADIO = 'radio';
    private const OPTION_TYPE_CHECKBOX = 'checkbox';
    private const OPTION_TYPE_MULTI = 'multi';

    private \Magento\Bundle\Model\Option $option;
    private array $selections = [];

    public function __construct(\Magento\Bundle\Model\Option $option)
    {
        $this->option = $option;
    }

    public function addSelection(\M2E\TikTokShop\Model\Magento\Product\Bundle\SelectionWrapper $selection): self
    {
        $this->selections[] = $selection;

        return $this;
    }

    /**
     * @return array<\M2E\TikTokShop\Model\Magento\Product\Bundle\SelectionWrapper>
     */
    public function getSelections(): array
    {
        return $this->selections;
    }

    public function isMultiselect(): bool
    {
        return $this->option->getType() === self::OPTION_TYPE_CHECKBOX
            || $this->option->getType() === self::OPTION_TYPE_MULTI;
    }

    public function getOptionId(): int
    {
        return (int)$this->option->getOptionId();
    }

    public function getLabel(): string
    {
        return (string)$this->option->getTitle();
    }
}
