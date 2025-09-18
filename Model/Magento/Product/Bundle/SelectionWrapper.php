<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Bundle;

class SelectionWrapper
{
    private \Magento\Catalog\Model\Product $selection;
    /** @var \M2E\TikTokShop\Model\Magento\Product\Bundle\OptionWrapper */
    private OptionWrapper $option;

    public function __construct(\Magento\Catalog\Model\Product $selection, OptionWrapper $option)
    {
        $this->selection = $selection;
        $this->option = $option;
    }

    public function getSelectionId(): int
    {
        /** @psalm-suppress UndefinedMagicMethod */
        return (int)$this->selection->getSelectionId();
    }

    public function getProductId(): int
    {
        /** @psalm-suppress UndefinedMagicMethod */
        return (int)$this->selection->getProductId();
    }

    public function getLabel(): string
    {
        return $this->selection->getName();
    }

    public function getSku(): string
    {
        return $this->selection->getSku();
    }

    /**
     * @return \M2E\TikTokShop\Model\Magento\Product\Bundle\OptionWrapper
     */
    public function getOption(): OptionWrapper
    {
        return $this->option;
    }

    public function getOptionIdSelectionIdKey(): string
    {
        return $this->getOption()->getOptionId() . '::' . $this->getSelectionId();
    }
}
