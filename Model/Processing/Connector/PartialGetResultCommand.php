<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Connector;

class PartialGetResultCommand extends AbstractGetResultCommand
{
    private string $processingId;
    private int $part;

    public function __construct(string $processingId, int $part)
    {
        $this->processingId = $processingId;
        $this->part = $part;
    }

    public function getRequestData(): array
    {
        return [
            'processing_id' => $this->processingId,
            'necessary_part' => $this->part,
        ];
    }
}
