<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

use M2E\TikTokShop\Model\Product;

class UpdateFromChannel
{
    private \M2E\TikTokShop\Model\Product\Repository $repository;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\Listing\LogService $logService;
    private int $logActionId;
    /** @var \M2E\TikTokShop\Model\Product\UpdateFromChannel\ProcessorFactory */
    private UpdateFromChannel\ProcessorFactory $changesProcessorFactory;

    public function __construct(
        Repository $repository,
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\Listing\LogService $logService,
        \M2E\TikTokShop\Model\Product\UpdateFromChannel\ProcessorFactory $changesProcessorFactory
    ) {
        $this->repository = $repository;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->changesProcessorFactory = $changesProcessorFactory;
    }

    public function process(
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductCollection $channelProductCollection,
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): void {
        if ($channelProductCollection->empty()) {
            return;
        }

        $existed = $this->repository->findByTtsProductIds(
            $channelProductCollection->getProductsIds(),
            $account->getId(),
            $shop->getId(),
        );

        foreach ($existed as $product) {
            $channelProduct = $channelProductCollection->get($product->getTTSProductId());

            $changesProcessor = $this->changesProcessorFactory->create($product, $channelProduct);

            $changeResult = $changesProcessor->processChanges();
            if ($changeResult->isChangedSomeVariant()) {
                $this->repository->saveVariantsSku($product->getVariants());
            }

            if ($changeResult->isChangedProduct()) {
                $this->repository->save($product);
            }

            $this->writeInstructions($changeResult->getInstructionsData());
            $this->writeLogs($product, $changeResult->getLogs());
        }
    }

    private function writeInstructions(array $instructionsData): void
    {
        if (empty($instructionsData)) {
            return;
        }

        $this->instructionService->createBatch($instructionsData);
    }

    /**
     * @param \M2E\TikTokShop\Model\Product $product
     * @param \M2E\TikTokShop\Model\Listing\Log\Record[] $records
     *
     * @return void
     */
    private function writeLogs(Product $product, array $records): void
    {
        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $this->logService->addRecordToProduct(
                $record,
                $product,
                \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );
        }
    }

    private function getLogActionId(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->logActionId ?? ($this->logActionId = $this->logService->getNextActionId());
    }
}
