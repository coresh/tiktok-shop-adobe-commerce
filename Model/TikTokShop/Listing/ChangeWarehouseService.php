<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing;

use M2E\TikTokShop\Model\Exception\Logic;

class ChangeWarehouseService
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\Listing\LogService $logService;
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\Listing\LogService $logService,
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository
    ) {
        $this->productRepository = $productRepository;
        $this->listingRepository = $listingRepository;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function change(\M2E\TikTokShop\Model\Listing $listing, int $warehouseId): void
    {
        $prevWarehouseId = $listing->getWarehouseId();
        $this->updateListingWarehouse($listing, $warehouseId);
        $this->addInstruction($listing->getId());
        $this->addChangeLog($listing, $prevWarehouseId, $warehouseId);
    }

    private function updateListingWarehouse(\M2E\TikTokShop\Model\Listing $listing, int $warehouseId): void
    {
        $listing->setWarehouseId($warehouseId);
        $this->listingRepository->save($listing);
    }

    private function addInstruction(int $listingId): void
    {
        $listingProductInstructionsData = [];
        foreach ($this->productRepository->findActiveIdsByListingId($listingId) as $productId) {
            $listingProductInstructionsData[] = [
                'listing_product_id' => $productId,
                'type' => \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_WAREHOUSE,
                'initiator' => \M2E\TikTokShop\Model\Listing::INSTRUCTION_INITIATOR_CHANGED_LISTING_WAREHOUSE,
                'priority' => 20,
            ];
        }

        $this->instructionService->createBatch($listingProductInstructionsData);
    }

    private function addChangeLog(\M2E\TikTokShop\Model\Listing $listing, ?int $prevWarehouseId, int $warehouseId): void
    {
        $this->logService->addRecordToListing(
            $this->prepareChangeRecord($prevWarehouseId, $warehouseId),
            $listing,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_EDIT_LISTING_SETTINGS,
            null
        );
    }

    private function prepareChangeRecord(
        ?int $prevWarehouseId,
        int $warehouseId
    ): \M2E\TikTokShop\Model\Listing\Log\Record {
        return new \M2E\TikTokShop\Model\Listing\Log\Record(
            (string)__(
                'The Warehouse for this listing was updated from \'%prev_warehouse\' to \'%current_warehouse\'.',
                [
                    'prev_warehouse' => $this->getWarehouseNameById($prevWarehouseId),
                    'current_warehouse' => $this->getWarehouseNameById($warehouseId),
                ]
            ),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    private function getWarehouseNameById(?int $warehouseId): string
    {
        if (!$warehouseId) {
            return 'N/A';
        }

        try {
            $warehouse = $this->warehouseRepository->get($warehouseId);
            $result = $warehouse->getName();
        } catch (Logic $e) {
            $result = 'N/A';
        }

        return $result;
    }
}
