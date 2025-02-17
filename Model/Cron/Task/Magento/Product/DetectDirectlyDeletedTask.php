<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Magento\Product;

class DetectDirectlyDeletedTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'magento/product/detect_directly_deleted';

    private \M2E\TikTokShop\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $otherRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\UnmapDeletedProduct $unmanagedUnmapDeletedProduct;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\UnmapDeletedProduct $unmanagedUnmapDeletedProduct,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $otherRepository,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct
    ) {
        $this->unmanagedUnmapDeletedProduct = $unmanagedUnmapDeletedProduct;
        $this->otherRepository = $otherRepository;
        $this->listingRemoveDeletedProduct = $listingRemoveDeletedProduct;
        $this->productRepository = $productRepository;
    }

    public function process($context): void
    {
        $processedIds = [];
        foreach ($this->productRepository->findRemovedMagentoProductIds(100) as $magentoProductId) {
            if (isset($processedIds[$magentoProductId])) {
                continue;
            }

            $processedIds[$magentoProductId] = true;

            $this->listingRemoveDeletedProduct->process($magentoProductId);
        }

        foreach ($this->otherRepository->findRemovedMagentoProductIds() as $magentoProductId) {
            $this->unmanagedUnmapDeletedProduct->process($magentoProductId);
        }
    }
}
