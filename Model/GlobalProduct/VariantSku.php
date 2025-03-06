<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct;

use M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku as GlobalVariantResource;

class VariantSku extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku::class);
    }

    public function setGlobalProductId(int $globalProductId): self
    {
        $this->setData(GlobalVariantResource::COLUMN_GLOBAL_PRODUCT_ID, $globalProductId);

        return $this;
    }

    public function setMagentoProductId(int $magentoProductId): self
    {
        $this->setData(GlobalVariantResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function setGlobalId(string $globalId): self
    {
        $this->setData(GlobalVariantResource::COLUMN_GLOBAL_ID, $globalId);

        return $this;
    }

    public function getGlobalId(): ?string
    {
        $data = $this->getData(GlobalVariantResource::COLUMN_GLOBAL_ID);
        if (empty($data)) {
            return null;
        }

        return (string)$data;
    }

    public function setSalesAttributes($salesAttributes): self
    {
        $this->setData(
            GlobalVariantResource::COLUMN_SALES_ATTRIBUTES,
            json_encode($salesAttributes, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getSalesAttributes(): array
    {
        $data = $this->getData(GlobalVariantResource::COLUMN_SALES_ATTRIBUTES);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setSellerSku(string $sellerSku): self
    {
        $this->setData(GlobalVariantResource::COLUMN_SELLER_SKU, $sellerSku);

        return $this;
    }

    public function getSellerSku(): string
    {
        return (string)$this->getData(GlobalVariantResource::COLUMN_SELLER_SKU);
    }

    public function setPrice(array $price): self
    {
        $this->setData(
            GlobalVariantResource::COLUMN_PRICE,
            json_encode($price, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getPrice(): array
    {
        $data = $this->getData(GlobalVariantResource::COLUMN_PRICE);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setIdentifierCode(array $identifierCode): self
    {
        $this->setData(
            GlobalVariantResource::COLUMN_IDENTIFIER_CODE,
            json_encode($identifierCode, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getIdentifierCode(): array
    {
        $data = $this->getData(GlobalVariantResource::COLUMN_IDENTIFIER_CODE);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }
}
