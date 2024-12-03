<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class DeleteService
{
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;
    private \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private \M2E\TikTokShop\Model\Product\Image\Relation\Repository $imageRelationRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Image\Relation\Repository $imageRelationRepository,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\TikTokShop\Model\Instruction\Repository $instructionRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService
    ) {
        $this->imageRelationRepository = $imageRelationRepository;
        $this->tagBuffer = $tagBuffer;
        $this->listingProductRepository = $listingProductRepository;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->instructionRepository = $instructionRepository;
        $this->listingLogService = $listingLogService;
    }

    public function process(
        \M2E\TikTokShop\Model\Product $product,
        $initiator
    ): void {
        $this->removeTags($product);

        $this->removeScheduledActions($product);
        $this->removeInstructions($product);

        $this->listingLogService->addProduct(
            $product,
            $initiator,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
            $this->listingLogService->getNextActionId(),
            (string)__('Item was Removed'),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );

        $this->imageRelationRepository->deleteByListingProductId($product->getId());

        foreach ($product->getVariants() as $variant) {
            $this->listingProductRepository->deleteVariantSku($variant);
        }

        $this->listingProductRepository->delete($product);
    }

    private function removeTags(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->tagBuffer->removeAllTags($product);
        $this->tagBuffer->flush();
    }

    private function removeScheduledActions(\M2E\TikTokShop\Model\Product $product): void
    {
        $scheduledAction = $this->scheduledActionRepository->findByListingProductId($product->getId());
        if ($scheduledAction !== null) {
            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function removeInstructions(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->instructionRepository->removeByListingProduct($product->getId());
    }
}
