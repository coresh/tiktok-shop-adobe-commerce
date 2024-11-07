<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\UpdateFromChannel;

class ChangeResult
{
    private \M2E\TikTokShop\Model\Product $product;
    private bool $isChangedProduct;
    private bool $isChangedSomeVariant;
    private array $instructionsData;
    /** @var \M2E\TikTokShop\Model\Listing\Log\Record[] */
    private array $logs;

    public function __construct(
        \M2E\TikTokShop\Model\Product $product,
        bool $isChangedProduct,
        bool $isChangedSomeVariant,
        array $instructionsData,
        array $logs
    ) {
        $this->product = $product;
        $this->isChangedProduct = $isChangedProduct;
        $this->isChangedSomeVariant = $isChangedSomeVariant;
        $this->instructionsData = $instructionsData;
        $this->logs = $logs;
    }

    public function getProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->product;
    }

    public function isChangedProduct(): bool
    {
        return $this->isChangedProduct;
    }

    public function isChangedSomeVariant(): bool
    {
        return $this->isChangedSomeVariant;
    }

    public function getInstructionsData(): array
    {
        return $this->instructionsData;
    }

    /**
     * @return \M2E\TikTokShop\Model\Listing\Log\Record[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
