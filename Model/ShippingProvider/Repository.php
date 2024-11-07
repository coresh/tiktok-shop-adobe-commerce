<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ShippingProvider;

use M2E\TikTokShop\Model\ResourceModel\ShippingProvider as ShippingProviderResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\ShippingProvider\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\ShippingProvider\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\TikTokShop\Model\ShippingProvider $shippingProvider): void
    {
        $shippingProvider->save();
    }

    public function save(\M2E\TikTokShop\Model\ShippingProvider $shippingProvider): void
    {
        $shippingProvider->save();
    }

    /**
     * @return \M2E\TikTokShop\Model\ShippingProvider[]
     */
    public function getByAccountShopWarehouse(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Warehouse $warehouse
    ): array {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingProviderResource::COLUMN_ACCOUNT_ID, ['eq' => $account->getId()])
                   ->addFieldToFilter(ShippingProviderResource::COLUMN_SHOP_ID, ['eq' => $shop->getId()])
                   ->addFieldToFilter(ShippingProviderResource::COLUMN_WAREHOUSE_ID, ['eq' => $warehouse->getId()]);

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    /**
     * @return \M2E\TikTokShop\Model\ShippingProvider[]
     */
    public function findByShippingProviderIds(array $shippingProviderIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ShippingProviderResource::COLUMN_SHIPPING_PROVIDER_ID,
            ['in' => $shippingProviderIds]
        );

        return array_values($collection->getItems());
    }

    public function findByShippingProviderId(string $shippingProviderId): ?\M2E\TikTokShop\Model\ShippingProvider
    {
        $providers = $this->findByShippingProviderIds([$shippingProviderId]);
        if (empty($providers)) {
            return null;
        }

        return reset($providers);
    }
}
