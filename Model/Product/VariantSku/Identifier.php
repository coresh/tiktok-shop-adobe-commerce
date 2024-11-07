<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\VariantSku;

class Identifier
{
    private string $id;
    private string $type;

    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
