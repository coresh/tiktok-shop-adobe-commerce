<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get;

class Response
{
    /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Attribute[] */
    private array $attributes;
    private array $rules;

    public function __construct(array $attributes, array $rules)
    {
        $this->rules = $rules;
        $this->attributes = $attributes;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
