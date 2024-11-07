<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity;

class OrderBuilder
{
    private string $ttsOrderId;
    private array $orderItems = [];

    public static function create(): self
    {
        return new self();
    }

    public function build(): Order
    {
        $this->validate();

        return new Order(
            $this->ttsOrderId,
            $this->orderItems,
        );
    }

    public function setOrderId(string $ttsOrderId): self
    {
        $this->ttsOrderId = $ttsOrderId;

        return $this;
    }

    public function addOrderItem(string $itemId, ?string $packageId): self
    {
        $this->orderItems[] = [
            'id' => $itemId,
            'package_id' => $packageId,
        ];

        return $this;
    }

    public function getItemsCount(): int
    {
        return count($this->orderItems);
    }

    private function validate(): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->ttsOrderId)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('TikTok Shop order ID not set');
        }

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (empty($this->orderItems)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('TikTok Shop order items is empty');
        }
    }
}
