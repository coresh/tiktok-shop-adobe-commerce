<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Instruction;

class Processor
{
    private \M2E\TikTokShop\Model\Config\Manager $configManager;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Instruction\Handler\InputFactory $handlerInputFactory;
    private SynchronizationTemplate\Handler $synchronizationTemplateHandler;
    private \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository,
        \M2E\TikTokShop\Model\Config\Manager $configManager,
        SynchronizationTemplate\Handler $synchronizationTemplateHandler,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\Instruction\Handler\InputFactory $handlerInputFactory,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        $this->configManager = $configManager;
        $this->synchronizationTemplateHandler = $synchronizationTemplateHandler;
        $this->exceptionHelper = $exceptionHelper;
        $this->handlerInputFactory = $handlerInputFactory;
        $this->instructionRepository = $instructionRepository;
        $this->productRepository = $productRepository;
    }

    public function process(): void
    {
        $this->deleteInstructionsOlderThenWeek();
        $this->deleteInstructionsWithoutListingProducts();

        $listingsProductsById = $this->loadListingsProducts();
        if (empty($listingsProductsById)) {
            return;
        }

        $instructionsGroupedByListingProductId = $this->loadInstructions($listingsProductsById);
        if (empty($instructionsGroupedByListingProductId)) {
            return;
        }

        foreach ($instructionsGroupedByListingProductId as $listingProductId => $listingProductInstructions) {
            try {
                $handlerInput = $this->handlerInputFactory->create(
                    $listingsProductsById[$listingProductId],
                    $listingProductInstructions
                );

                $this->synchronizationTemplateHandler->process($handlerInput);
                if ($handlerInput->getListingProduct()->isDeleted()) {
                    break;
                }
            } catch (\Throwable $exception) {
                $this->exceptionHelper->process($exception);
            }

            $this->instructionRepository->removeByIds(array_keys($listingProductInstructions));
        }
    }

    /**
     * @return \M2E\TikTokShop\Model\Product[]
     * @throws \Exception
     */
    private function loadListingsProducts(): array
    {
        $maxListingsProductsCount = (int)$this->configManager->getGroupValue(
            '/listing/product/instructions/cron/',
            'listings_products_per_one_time',
        );

        $ids = $this->instructionRepository->findListingProductIdsByPriority($maxListingsProductsCount, null);
        if (empty($ids)) {
            return [];
        }

        $result = [];
        foreach ($this->productRepository->findByIds($ids) as $product) {
            $result[(int)$product->getId()] = $product;
        }

        return $result;
    }

    /**
     * @param \M2E\TikTokShop\Model\Product[] $listingsProductsById
     *
     * @return \M2E\TikTokShop\Model\Instruction[][]
     */
    private function loadInstructions(array $listingsProductsById): array
    {
        if (empty($listingsProductsById)) {
            return [];
        }

        $instructions = $this->instructionRepository->findByListingProducts(array_keys($listingsProductsById), null);

        $instructionsByListingsProducts = [];
        foreach ($instructions as $instruction) {
            $listingProduct = $listingsProductsById[$instruction->getListingProductId()];
            $instruction->initListingProduct($listingProduct);

            $instructionsByListingsProducts[$instruction->getListingProductId()][$instruction->getId()] = $instruction;
        }

        return $instructionsByListingsProducts;
    }

    private function deleteInstructionsWithoutListingProducts(): void
    {
        $this->instructionRepository->removeWithoutListingProduct();
    }

    private function deleteInstructionsOlderThenWeek(): void
    {
        $greaterThenDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        $greaterThenDate->modify('-7 day');

        $this->instructionRepository->removeOld($greaterThenDate);
    }
}
