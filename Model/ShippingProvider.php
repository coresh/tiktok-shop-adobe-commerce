<?php

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\ShippingProvider as ShippingProviderResource;

class ShippingProvider extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ShippingProviderResource::class);
    }

    public function create(
        Account $account,
        Shop $shop,
        Warehouse $warehouse,
        string $deliveryOptionIdId,
        string $shippingProviderId,
        string $shippingProviderName
    ): self {
        $this->setData(ShippingProviderResource::COLUMN_ACCOUNT_ID, $account->getId())
             ->setData(ShippingProviderResource::COLUMN_SHOP_ID, $shop->getId())
             ->setData(ShippingProviderResource::COLUMN_WAREHOUSE_ID, $warehouse->getId())
             ->setData(ShippingProviderResource::COLUMN_DELIVERY_OPTION_ID, $deliveryOptionIdId)
             ->setData(ShippingProviderResource::COLUMN_SHIPPING_PROVIDER_ID, $shippingProviderId)
             ->setData(ShippingProviderResource::COLUMN_SHIPPING_PROVIDER_NAME, $shippingProviderName);

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ShippingProviderResource::COLUMN_ACCOUNT_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getData(ShippingProviderResource::COLUMN_SHOP_ID);
    }

    public function getWarehouseId(): int
    {
        return (int)$this->getData(ShippingProviderResource::COLUMN_WAREHOUSE_ID);
    }

    public function getDeliveryOptionId(): string
    {
        return (string)$this->getData(ShippingProviderResource::COLUMN_DELIVERY_OPTION_ID);
    }

    public function getShippingProviderId(): string
    {
        return $this->getData(ShippingProviderResource::COLUMN_SHIPPING_PROVIDER_ID);
    }

    public function getShippingProviderName(): string
    {
        return (string)$this->getData(ShippingProviderResource::COLUMN_SHIPPING_PROVIDER_NAME);
    }
}
