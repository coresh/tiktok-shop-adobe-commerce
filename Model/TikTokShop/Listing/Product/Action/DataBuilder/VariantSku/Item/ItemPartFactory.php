<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku\Item;

class ItemPartFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createIdentifier(string $code, string $type): Identifier
    {
        return $this->objectManager->create(Identifier::class, [
            'code' => $code,
            'type' => $type,
        ]);
    }

    public function createSalesAttribute(
        string $name,
        string $valueName,
        ?\M2E\TikTokShop\Model\Magento\Product\Image $image = null
    ): SalesAttribute {
        return $this->objectManager->create(SalesAttribute::class, [
            'name' => $name,
            'valueName' => $valueName,
            'image' => $image,
        ]);
    }

    public function createPrice(float $amount, string $currency): Price
    {
        return $this->objectManager->create(Price::class, [
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }

    public function createInventory(string $warehouseId, int $quantity): Inventory
    {
        return $this->objectManager->create(Inventory::class, [
            'warehouseId' => $warehouseId,
            'quantity' => $quantity,
        ]);
    }
}
