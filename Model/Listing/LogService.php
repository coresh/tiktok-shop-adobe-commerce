<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

class LogService
{
    /** @var \M2E\TikTokShop\Model\Listing\LogFactory */
    private LogFactory $logFactory;
    /** @var \M2E\TikTokShop\Model\Listing\Log\Repository */
    private Log\Repository $repository;
    private \M2E\TikTokShop\Model\Registry\Manager $registry;

    public function __construct(
        LogFactory $logFactory,
        \M2E\TikTokShop\Model\Listing\Log\Repository $repository,
        \M2E\TikTokShop\Model\Registry\Manager $registry
    ) {
        $this->logFactory = $logFactory;
        $this->repository = $repository;
        $this->registry = $registry;
    }

    public function addRecordToProduct(
        Log\Record $record,
        \M2E\TikTokShop\Model\Product $listingProduct,
        int $initiator,
        int $action,
        ?int $actionId
    ): void {
        $this->addProduct(
            $listingProduct,
            $initiator,
            $action,
            $actionId,
            $record->getMessage(),
            $record->getType(),
        );
    }

    public function addProduct(
        \M2E\TikTokShop\Model\Product $listingProduct,
        int $initiator,
        int $action,
        ?int $actionId,
        string $description,
        int $type
    ): void {
        $log = $this->logFactory->create();
        $log->createProduct($listingProduct, $initiator, $action, (int)$actionId, $description, $type);

        $this->repository->create($log);
    }

    public function addRecordToListing(
        Log\Record $record,
        \M2E\TikTokShop\Model\Listing $listing,
        int $initiator,
        int $action,
        ?int $actionId
    ): void {
        $this->addListing(
            $listing,
            $initiator,
            $action,
            $actionId,
            $record->getMessage(),
            $record->getType(),
        );
    }

    public function addListing(
        \M2E\TikTokShop\Model\Listing $listing,
        int $initiator,
        int $action,
        ?int $actionId,
        string $description,
        int $type
    ): void {
        $log = $this->logFactory->create();
        $log->createListing($listing, $initiator, $action, (int)$actionId, $description, $type);

        $this->repository->create($log);
    }

    // ----------------------------------------

    public function getNextActionId(): int
    {
        $lastValue = (int)$this->registry->getValue($registryKey = 'log/listing/last_action_id');

        $nextId = $lastValue + 1;

        $this->registry->setValue($registryKey, (string)$nextId);

        return $nextId;
    }
}
