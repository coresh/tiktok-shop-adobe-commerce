<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\Checker;

class Result
{
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator;
    private array $tags;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        array $tags
    ) {
        $this->configurator = $configurator;
        $this->tags = $tags;
    }

    public function getConfigurator(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
