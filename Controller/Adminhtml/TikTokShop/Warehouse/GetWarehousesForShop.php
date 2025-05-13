<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Warehouse;

class GetWarehousesForShop extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount
{
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository
    ) {
        parent::__construct();

        $this->warehouseRepository = $warehouseRepository;
    }

    public function execute()
    {
        $shopId = $this->getRequest()->getParam('shop_id');

        if (empty($shopId)) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Shop Id is required',
            ]);

            return $this->getResult();
        }

        $warehouses = $this->warehouseRepository->findByShop((int)$shopId);
        $warehouses = array_map(static function (\M2E\TikTokShop\Model\Warehouse $entity) {
            return [
                'value' => $entity->getId(),
                'label' => $entity->getName(),
            ];
        }, $warehouses);

        $this->setJsonContent([
            'result' => true,
            'warehouses' => $warehouses,
        ]);

        return $this->getResult();
    }
}
