<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class RemoveHandler
{
    private \M2E\TikTokShop\Model\Product\DeleteService $productDeleteService;
    /** @var \M2E\TikTokShop\Model\Product\Repository */
    private Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\DeleteService $productDeleteService,
        Repository $productRepository
    ) {
        $this->productDeleteService = $productDeleteService;
        $this->productRepository = $productRepository;
    }

    public function process(
        \M2E\TikTokShop\Model\Product $listingProduct,
        $initiator
    ): void {
        if (!$listingProduct->isStatusNotListed()) {
            $listingProduct->setStatusNotListed(\M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER);

            $this->productRepository->save($listingProduct);
        }

        $this->productDeleteService->process($listingProduct, $initiator);
    }
}
