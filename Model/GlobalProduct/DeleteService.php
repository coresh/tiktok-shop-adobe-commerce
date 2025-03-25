<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct;

class DeleteService
{
    private \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository;

    public function __construct(Repository $globalProductRepository)
    {
        $this->globalProductRepository = $globalProductRepository;
    }

    public function byAccount(int $accountId): void
    {
        $globalProducts = $this->globalProductRepository->getByAccountId($accountId);
        $this->deleteGlobalProducts($globalProducts);
    }

    public function byMagentoProductId(int $magentoProductId): void
    {
        $globalProducts = $this->globalProductRepository->getByMagentoProductId($magentoProductId);
        $this->deleteGlobalProducts($globalProducts);
    }

    /**
     * @param \M2E\TikTokShop\Model\GlobalProduct[] $globalProducts
     */
    private function deleteGlobalProducts(array $globalProducts): void
    {
        foreach ($globalProducts as $globalProduct) {
            $this->globalProductRepository->delete($globalProduct);
        }
    }

    public function variantByMagentoProductId(int $magentoProductId)
    {
        $variants = $this->globalProductRepository->getVariantsByMagentoProductId($magentoProductId);
        if (empty($variants)) {
            return;
        }

        foreach ($variants as $variant) {
            $this->globalProductRepository->deleteVariantSku($variant);
        }
    }
}
