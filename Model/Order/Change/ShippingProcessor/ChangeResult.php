<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Change\ShippingProcessor;

class ChangeResult
{
    public bool $isSuccess;
    public bool $isSkipped;

    public string $trackingNumber;
    public string $shippingProviderName;
    /** @var \M2E\TikTokShop\Model\Response\Message[] */
    public array $messages;
    /** @var \M2E\TikTokShop\Model\Order\Item[] */
    public array $orderItems;
    public ?string $packageId;

    private function __construct(
        bool $isSuccess,
        bool $isSkipped,
        array $messages,
        array $orderItems,
        ?string $packageId,
        string $trackingNumber,
        string $shippingProviderName
    ) {
        $this->isSuccess = $isSuccess;
        $this->trackingNumber = $trackingNumber;
        $this->shippingProviderName = $shippingProviderName;
        $this->isSkipped = $isSkipped;
        $this->messages = $messages;
        $this->orderItems = $orderItems;
        $this->packageId = $packageId;
    }

    public static function createSkipped(): self
    {
        return new self(true, true, [], [], null, '', '');
    }

    public static function createSuccess(
        array $orderItems,
        ?string $packageId,
        string $trackingNumber,
        string $shippingProviderName,
        array $messages
    ): self {
        return new self(true, false, $messages, $orderItems, $packageId, $trackingNumber, $shippingProviderName);
    }

    public static function createFailed(
        array $orderItems,
        string $trackingNumber,
        string $shippingProviderName,
        array $messages
    ): self {
        return new self(false, false, $messages, $orderItems, null, $trackingNumber, $shippingProviderName);
    }
}
