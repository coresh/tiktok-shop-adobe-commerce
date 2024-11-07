<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku;

class Item
{
    private string $sellerSku;
    private ?string $skuId = null;
    private Item\Price $price;
    private ?Item\Identifier $identifier = null;
    private array $salesAttributes = [];
    private array $inventories = [];

    public function setSellerSku(string $sellerSku)
    {
        $this->sellerSku = $sellerSku;
    }

    public function getSellerSku(): string
    {
        return $this->sellerSku;
    }

    public function setSkuId(string $skuId): void
    {
        $this->skuId = $skuId;
    }

    public function setIdentifier(Item\Identifier $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function setPrice(Item\Price $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): Item\Price
    {
        return $this->price;
    }

    public function addSalesAttribute(Item\SalesAttribute $salesAttribute)
    {
        $this->salesAttributes[] = $salesAttribute;
    }

    /**
     * @return Item\SalesAttribute[]
     */
    public function getSalesAttributes(): array
    {
        return $this->salesAttributes;
    }

    public function addInventory(Item\Inventory $inventory)
    {
        $this->inventories[] = $inventory;
    }

    /**
     * @return Item\Inventory[]
     */
    public function getInventories(): array
    {
        return $this->inventories;
    }

    public function toArray(): array
    {
        $data = [
            'seller_sku' => $this->sellerSku,
            'price' => $this->price->toArray(),
            'inventory' => array_map(
                fn(Item\Inventory $inventory) => $inventory->toArray(),
                $this->inventories
            ),
            'sales_attributes' => array_map(
                fn(Item\SalesAttribute $salesAttribute) => $salesAttribute->toArray(),
                $this->salesAttributes
            ),
        ];

        if ($this->skuId !== null) {
            $data['id'] = $this->skuId;
        }

        if ($this->identifier !== null) {
            $data['identifier_code'] = [
                'code' => $this->identifier->getCode(),
                'type' => $this->identifier->getType(),
            ];
        }

        return $data;
    }

    public function getIdentifier(): ?Item\Identifier
    {
        return $this->identifier;
    }
}
