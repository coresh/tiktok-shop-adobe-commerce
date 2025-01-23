<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class InspectDirectChanges
{
    public const INSTRUCTION_TYPE = 'inspector_triggered';
    public const INSTRUCTION_INITIATOR = 'direct_changes_inspector';
    public const INSTRUCTION_PRIORITY = 10;

    private \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config $config;
    private \M2E\TikTokShop\Model\Product\InspectDirectChanges\Context $context;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;

    public function __construct(
        \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config $config,
        \M2E\TikTokShop\Model\Product\InspectDirectChanges\Context $context,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository,
        \M2E\TikTokShop\Model\InstructionService $instructionService
    ) {
        $this->config = $config;
        $this->context = $context;
        $this->productRepository = $productRepository;
        $this->instructionRepository = $instructionRepository;
        $this->instructionService = $instructionService;
    }

    public function process(): void
    {
        $allowedListingsProductsCount = $this->calculateAllowedProductsCount();
        if (empty($allowedListingsProductsCount)) {
            return;
        }

        $productIds = $this->productRepository->getIds(
            $this->context->getLastProductId(),
            $allowedListingsProductsCount
        );
        if (empty($productIds)) {
            $this->context->setLastProductId(0);

            return;
        }

        $instructionsData = [];

        foreach ($productIds as $productId) {
            $instructionsData[] = [
                'listing_product_id' => $productId,
                'type' => self::INSTRUCTION_TYPE,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => self::INSTRUCTION_PRIORITY,
            ];
        }

        $this->instructionService->createBatch($instructionsData);

        $this->context->setLastProductId(max($productIds));
    }

    private function calculateAllowedProductsCount(): int
    {
        $maxAllowedInstructionsCount = $this->config->getMaxAllowedProducts();
        $currentInstructionsCount = $this->instructionRepository->getInstructionCountByInitiator(self::INSTRUCTION_INITIATOR);

        if ($currentInstructionsCount > $maxAllowedInstructionsCount) {
            return 0;
        }

        return $maxAllowedInstructionsCount - $currentInstructionsCount;
    }
}
