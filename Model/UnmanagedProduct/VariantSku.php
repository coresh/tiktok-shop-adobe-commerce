<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct;

use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku as UnmanagedVariantSkuResource;

class VariantSku extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttributeFactory $salesAttributeFactory;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttributeFactory $salesAttributeFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->salesAttributeFactory = $salesAttributeFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku::class);
    }

    public function init(
        \M2E\TikTokShop\Model\UnmanagedProduct $product,
        ?\M2E\TikTokShop\Model\Warehouse $warehouse,
        int $status,
        string $skuID,
        string $sku,
        int $qty,
        float $currentPrice,
        string $currency,
        ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier,
        array $salesAttributes
    ): self {
        $this
            ->setData(UnmanagedVariantSkuResource::COLUMN_PRODUCT_ID, $product->getId())
            ->setData(UnmanagedVariantSkuResource::COLUMN_ACCOUNT_ID, $product->getAccountId())
            ->setData(UnmanagedVariantSkuResource::COLUMN_SHOP_ID, $product->getShopId())
            ->setWarehouseId($warehouse)
            ->setStatus($status)
            ->setSkuId($skuID)
            ->setSku($sku)
            ->setQty($qty)
            ->setCurrentPrice($currentPrice)
            ->setCurrency($currency)
            ->setIdentifier($identifier)
            ->setSalesAttributes($salesAttributes);

        return $this;
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getProductId(): int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_PRODUCT_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_SHOP_ID);
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_ACCOUNT_ID);
    }

    public function unmapVariant(): void
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_MAGENTO_PRODUCT_ID, null);
    }

    public function mapToMagentoProduct(int $magentoProductId): void
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function getMagentoProductId(): ?int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function setSkuId(string $value): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_SKU_ID, $value);

        return $this;
    }

    public function getSkuId(): string
    {
        return (string)$this->getData(UnmanagedVariantSkuResource::COLUMN_SKU_ID);
    }

    public function hasMagentoProductId(): bool
    {
        return !empty($this->getMagentoProductId());
    }

    public function setWarehouseId(?\M2E\TikTokShop\Model\Warehouse $warehouse): self
    {
        $warehouseId = !empty($warehouse) ? $warehouse->getId() : null;

        $this->setData(UnmanagedVariantSkuResource::COLUMN_WAREHOUSE_ID, $warehouseId);

        return $this;
    }

    public function getWarehouseId(): int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_WAREHOUSE_ID);
    }

    public function setSku(string $value): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_SKU, $value);

        return $this;
    }

    public function getSku(): string
    {
        return $this->getData(UnmanagedVariantSkuResource::COLUMN_SKU);
    }

    public function setCurrentPrice(float $value): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_PRICE, $value);

        return $this;
    }

    public function getCurrentPrice(): float
    {
        return (float)$this->getData(UnmanagedVariantSkuResource::COLUMN_PRICE);
    }

    public function setCurrency(string $value): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_CURRENCY, $value);

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->getData(UnmanagedVariantSkuResource::COLUMN_CURRENCY);
    }

    /**
     * @param \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttribute[] $values
     *
     * @return $this
     */
    public function setSalesAttributes(array $values): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_SALES_ATTRIBUTES, json_encode($values, JSON_THROW_ON_ERROR));

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttribute[]
     */
    public function getSalesAttributes(): array
    {
        $json = $this->getData(UnmanagedVariantSkuResource::COLUMN_SALES_ATTRIBUTES);
        if ($json === null) {
            return [];
        }

        $salesAttributesData = json_decode($json, true);

        $salesAttributes = [];
        foreach ($salesAttributesData as $salesAttribute) {
            $salesAttributes[] = $this->salesAttributeFactory->create($salesAttribute);
        }

        return $salesAttributes;
    }

    //region Identifier
    public function setIdentifier(?\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier): self
    {
        if ($identifier === null) {
            return $this->resetIdentifier();
        }

        $this->setData(UnmanagedVariantSkuResource::COLUMN_IDENTIFIER_ID, $identifier->getId());
        $this->setData(UnmanagedVariantSkuResource::COLUMN_IDENTIFIER_TYPE, $identifier->getType());

        return $this;
    }

    public function getIdentifier(): ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier
    {
        $id = $this->getData(UnmanagedVariantSkuResource::COLUMN_IDENTIFIER_ID);
        $type = $this->getData(UnmanagedVariantSkuResource::COLUMN_IDENTIFIER_TYPE);

        if (!$id || !$type) {
            return null;
        }

        return new \M2E\TikTokShop\Model\Product\VariantSku\Identifier($id, $type);
    }

    private function resetIdentifier(): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_IDENTIFIER_ID, null);
        $this->setData(UnmanagedVariantSkuResource::COLUMN_IDENTIFIER_TYPE, null);

        return $this;
    }
    //endregion

    public function setQty(int $value): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_QTY, $value);

        return $this;
    }

    public function getQty(): int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_QTY);
    }

    public function setStatus(int $status): self
    {
        $this->setData(UnmanagedVariantSkuResource::COLUMN_STATUS, $status);

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData(UnmanagedVariantSkuResource::COLUMN_STATUS);
    }
}
