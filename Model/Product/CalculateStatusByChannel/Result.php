<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\CalculateStatusByChannel;

class Result
{
    private int $status;
    private \M2E\TikTokShop\Model\Listing\Log\Record $messageAboutChange;
    private int $statusChanger;
    private \M2E\TikTokShop\Model\Product $product;

    public function __construct(
        \M2E\TikTokShop\Model\Product $product,
        int $status,
        \M2E\TikTokShop\Model\Listing\Log\Record $messageAboutChange
    ) {
        $this->product = $product;
        $this->status = $status;
        $this->messageAboutChange = $messageAboutChange;
        $this->statusChanger = \M2E\TikTokShop\Model\Product::STATUS_CHANGER_COMPONENT;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getMessageAboutChange(): \M2E\TikTokShop\Model\Listing\Log\Record
    {
        return $this->messageAboutChange;
    }

    public function getInstructionData(): array
    {
        return [
            'listing_product_id' => $this->product->getId(),
            'type' => \M2E\TikTokShop\Model\Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            'initiator' => 'channel_changes_synchronization',
            'priority' => 60,
        ];
    }

    public function getStatusChanger(): int
    {
        return $this->statusChanger;
    }
}
