<?php

namespace M2E\TikTokShop\Model\Cron\Task\Product;

class InspectDirectChanges extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'product/inspect_direct_changes';

    public const KEY_PREFIX = '/listing/product/inspector';

    public const INSTRUCTION_TYPE = 'inspector_triggered';
    public const INSTRUCTION_INITIATOR = 'direct_changes_inspector';
    public const INSTRUCTION_PRIORITY = 10;

    private \M2E\TikTokShop\Model\Registry\Manager $register;
    private \M2E\TikTokShop\Model\Config\Manager $config;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository,
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\Registry\Manager $register,
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger,
        \M2E\TikTokShop\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource,
        );

        $this->register = $register;
        $this->config = $config;
        $this->instructionService = $instructionService;
        $this->instructionRepository = $instructionRepository;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function isModeEnabled(): bool
    {
        return false;
    }

    protected function performActions(): void
    {
        $allowedListingsProductsCount = $this->calculateAllowedListingsProductsCount();
        if ($allowedListingsProductsCount <= 0) {
            return;
        }

        $listingsProductsIds = $this->getNextListingsProductsIds($allowedListingsProductsCount);
        if (empty($listingsProductsIds)) {
            $this->setLastListingProductId(0);

            return;
        }

        $instructionsData = [];

        foreach ($listingsProductsIds as $listingProductId) {
            $instructionsData[] = [
                'listing_product_id' => $listingProductId,
                'type' => self::INSTRUCTION_TYPE,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => self::INSTRUCTION_PRIORITY,
            ];
        }

        $this->instructionService->createBatch($instructionsData);

        $this->setLastListingProductId(end($listingsProductsIds));
    }

    //########################################

    protected function calculateAllowedListingsProductsCount(): int
    {
        $maxAllowedInstructionsCount = (int)$this->config->getGroupValue(
            self::KEY_PREFIX . '/',
            'max_allowed_instructions_count'
        );

        $currentInstructionsCount = $this->instructionRepository->getCountByInitiator(self::INSTRUCTION_INITIATOR);
        if ($currentInstructionsCount > $maxAllowedInstructionsCount) {
            return 0;
        }

        return $maxAllowedInstructionsCount - $currentInstructionsCount;
    }

    protected function getNextListingsProductsIds(int $limit): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->getSelect()->order(['id ASC']);
        $collection->getSelect()->limit($limit);

        return $collection->getColumnValues('id');
    }

    protected function getLastListingProductId(): int
    {
        $configValue = $this->register->getValue(
            self::KEY_PREFIX . '/last_listing_product_id/'
        );

        if ($configValue === null) {
            return 0;
        }

        return (int)$configValue;
    }

    protected function setLastListingProductId($listingProductId): void
    {
        $this->register->setValue(
            self::KEY_PREFIX . '/last_listing_product_id/',
            (int)$listingProductId
        );
    }
}
