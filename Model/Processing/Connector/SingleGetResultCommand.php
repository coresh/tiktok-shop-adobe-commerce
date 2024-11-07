<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Connector;

class SingleGetResultCommand extends AbstractGetResultCommand
{
    /** @var string[] */
    private array $processingIds;

    public function __construct(array $processingIds)
    {
        $this->processingIds = $processingIds;
    }

    public function getRequestData(): array
    {
        return [
            'processing_ids' => $this->processingIds,
        ];
    }
}
