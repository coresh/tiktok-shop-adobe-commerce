<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Warehouse;

class ShippingMappingUpdater
{
    private Repository $repository;
    private \M2E\TikTokShop\Model\Warehouse\ShippingMappingFactory $shippingMappingFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Warehouse\ShippingMappingFactory $shippingMappingFactory,
        Repository $repository
    ) {
        $this->shippingMappingFactory = $shippingMappingFactory;
        $this->repository = $repository;
    }

    public function update(string $warehouseId, array $data): void
    {
        $preparedData = $this->prepareShippingProviderData($data);

        $warehouse = $this->repository->findByWarehouseId($warehouseId);
        if ($warehouse === null) {
            throw new \M2E\TikTokShop\Model\Exception(
                sprintf(
                    'Warehouse with id %s not found',
                    $warehouseId
                )
            );
        }
        $warehouse->setShippingProviderMapping($preparedData);

        $this->repository->save($warehouse);
    }

    private function prepareShippingProviderData(array $data): \M2E\TikTokShop\Model\Warehouse\ShippingMapping
    {
        foreach ($data as $carrierCode => $shippingProviderId) {
            if (empty($shippingProviderId)) {
                unset($data[$carrierCode]);
            }
        }

        return $this->shippingMappingFactory->create($data);
    }
}
