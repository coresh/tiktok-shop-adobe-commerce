<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Warehouse;

use M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\Warehouse as TikTokWarehouse;
use M2E\TikTokShop\Model\Warehouse as Entity;

class SynchronizeService
{
    private static array $typeMap = [
        TikTokWarehouse::TYPE_SALES_WAREHOUSE => Entity::TYPE_SALES_WAREHOUSE,
        TikTokWarehouse::TYPE_RETURN_WAREHOUSE => Entity::TYPE_RETURN_WAREHOUSE,
    ];

    private static array $subTypesMap = [
        TikTokWarehouse::SUB_TYPE_DOMESTIC_WAREHOUSE => Entity::SUB_TYPE_DOMESTIC_WAREHOUSE,
        TikTokWarehouse::SUB_TYPE_CB_OVERSEA_WAREHOUSE => Entity::SUB_TYPE_CB_OVERSEA_WAREHOUSE,
        TikTokWarehouse::SUB_TYPE_CB_DIRECT_SHIPPING_WAREHOUSE => Entity::SUB_TYPE_CB_DIRECT_SHIPPING_WAREHOUSE,
    ];

    private static array $effectStatusesMap = [
        TikTokWarehouse::EFFECT_STATUS_ENABLED => Entity::EFFECT_STATUS_EFFECTIVE,
        TikTokWarehouse::EFFECT_STATUS_DISABLED => Entity::EFFECT_STATUS_NONEFFECTIVE,
        TikTokWarehouse::EFFECT_STATUS_RESTRICTED => Entity::EFFECT_STATUS_RESTRICTED,
        TikTokWarehouse::EFFECT_STATUS_HOLIDAY_MODE => Entity::EFFECT_STATUS_HOLIDAY_MODE,
        TikTokWarehouse::EFFECT_STATUS_ORDER_LIMIT_MODE => Entity::EFFECT_STATUS_ORDER_LIMIT_MODE,
    ];

    private \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetList\Processor $connectionProcessor;
    private Repository $repository;
    private \M2E\TikTokShop\Model\WarehouseFactory $warehouseFactory;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetList\Processor $processor,
        \M2E\TikTokShop\Model\WarehouseFactory $warehouseFactory,
        Repository $repository
    ) {
        $this->connectionProcessor = $processor;
        $this->repository = $repository;
        $this->warehouseFactory = $warehouseFactory;
    }

    public function sync(\M2E\TikTokShop\Model\Account $account, \M2E\TikTokShop\Model\Shop $shop): void
    {
        $response = $this->connectionProcessor->process(
            $account,
            $shop,
        );

        /** @var \M2E\TikTokShop\Model\Warehouse[] $exist */
        $exists = [];
        foreach ($shop->getWarehouses() as $warehouse) {
            $exists[$warehouse->getWarehouseId()] = $warehouse;
        }

        foreach ($response->getWarehouses() as $responseWarehouse) {
            if (isset($exists[$responseWarehouse->getId()])) {
                $exist = $exists[$responseWarehouse->getId()];

                if (
                    $responseWarehouse->getName() !== $exist->getName()
                    || $responseWarehouse->isDefault() !== $exist->isDefault()
                ) {
                    $exist->setName($responseWarehouse->getName())
                          ->setIsDefault($responseWarehouse->isDefault());

                    $this->repository->save($exist);
                }

                continue;
            }

            $warehouse = $this->warehouseFactory->create();
            $warehouse->create(
                $shop,
                $responseWarehouse->getId(),
                $responseWarehouse->getName(),
                self::$effectStatusesMap[$responseWarehouse->getEffectStatus()],
                self::$typeMap[$responseWarehouse->getType()],
                self::$subTypesMap[$responseWarehouse->getSubType()],
                $responseWarehouse->isDefault(),
                $responseWarehouse->getAddress(),
            );

            $this->repository->create($warehouse);

            $exists[$warehouse->getWarehouseId()] = $warehouse;
        }

        $shop->setWarehouses(array_values($exists));
    }
}
