<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\GetList;

class Response
{
    /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\Warehouse[] */
    private array $warehouses = [];

    public function addWarehouse(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\Warehouse $warehouse
    ): void {
        $this->warehouses[] = $warehouse;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Warehouse\Warehouse[]
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }
}
