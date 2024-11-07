<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Get;

class Response
{
    private array $issues;

    /**
     * @param \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[] $issues
     */
    public function __construct(
        array $issues
    ) {
        $this->issues = $issues;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }
}
