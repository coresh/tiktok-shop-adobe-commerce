<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Warehouse;

class ShippingMapping
{
    private array $shippingMappings;

    /**
     * @param array<string, string> $shippingMappings key - magento carrier code, value - tts shipping provider id;
     */
    public function __construct(array $shippingMappings = [])
    {
        $this->shippingMappings = $shippingMappings;
    }

    public function isConfigured(): bool
    {
        return !empty($this->shippingMappings);
    }

    public function getProviderIdByCarrierCode(string $carrierCode): ?string
    {
        if (!array_key_exists($carrierCode, $this->shippingMappings)) {
            return null;
        }

        return (string)$this->shippingMappings[$carrierCode];
    }

    public function getCarrierCodeByProviderId(string $shippingProviderId): ?string
    {
        foreach ($this->shippingMappings as $carrierCode => $providerId) {
            if ($shippingProviderId === $providerId) {
                return $carrierCode;
            }
        }

        return null;
    }

    public function getDefaultProviderId(): ?string
    {
        return $this->shippingMappings['default'] ?? null;
    }

    public function toArray(): array
    {
        return $this->shippingMappings;
    }
}
