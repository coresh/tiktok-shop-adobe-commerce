<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku\Item;

class Price
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function toArray(): array
    {
        return [
            'amount' => (string)$this->amount,
            'currency' => $this->currency,
        ];
    }
}
