<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Warehouse;

class WarehouseOptionProvider
{
    private Repository $warehouseRepository;

    public function __construct(
        Repository $warehouseRepository
    ) {
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * @param int $shopId
     *
     * @return array<int, array{label: string, value: int}>
     */
    public function getOptionsByShopId(int $shopId): array
    {
        $warehouses = [];
        $entities = $this->warehouseRepository->findByShop($shopId);

        foreach ($entities as $entity) {
            $warehouses[$entity->getId()] = [
                'label' => $entity->getName(),
                'value' => $entity->getId(),
            ];
        }

        return $warehouses;
    }
}
