<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Channel;

class Promotion
{
    private string $promotionId;
    private string $title;
    private string $type;
    private string $status;
    private string $productLevel;
    private \DateTimeInterface $startDate;
    private \DateTimeInterface $endDate;

    /** @var \M2E\TikTokShop\Model\Promotion\Channel\Product[] */
    private array $promotionProducts;

    public function __construct(
        string $promotionId,
        string $title,
        string $type,
        string $status,
        string $productLevel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $promotionProducts
    ) {
        $this->promotionId = $promotionId;
        $this->title = $title;
        $this->type = $type;
        $this->status = $status;
        $this->productLevel = $productLevel;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->promotionProducts = $promotionProducts;
    }

    public function getPromotionId(): string
    {
        return $this->promotionId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getProductLevel(): string
    {
        return $this->productLevel;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion\Channel\Product[]
     */
    public function getPromotionProducts(): array
    {
        return $this->promotionProducts;
    }

    public function isStatusOngoing(): bool
    {
        return $this->getStatus() === \M2E\TikTokShop\Model\Promotion::STATUS_ONGOING;
    }
}
