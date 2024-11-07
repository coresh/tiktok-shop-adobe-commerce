<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

class TtsProductSkuCollection
{
    /** @var \M2E\TikTokShop\Model\Listing\Other\TtsProductSku[] */
    private array $variants = [];

    public function add(TtsProductSku $variant): void
    {
        $this->variants[$variant->getSkuId()] = $variant;
    }

    /**
     * @return \M2E\TikTokShop\Model\Listing\Other\TtsProductSku[]
     */
    public function getAll(): array
    {
        return array_values($this->variants);
    }

    public function getFirst(): \M2E\TikTokShop\Model\Listing\Other\TtsProductSku
    {
        return reset($this->variants);
    }

    public function count(): int
    {
        return count($this->variants);
    }
}
