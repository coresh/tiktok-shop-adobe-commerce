<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

class GlobalProductHandler
{
    private \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository;

    public function __construct(\M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository)
    {
        $this->globalProductRepository = $globalProductRepository;
    }

    public function handle(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\Core\Model\Connector\Response $response
    ) {
        $globalProductData = $response->getResponseData()['global_product'] ?? [];
        if (!$this->isNeedHandle($product, $globalProductData)) {
            return;
        }

        $globalProduct = $product->getGlobalProduct();

        $this->handleGlobalProductId($globalProduct, $globalProductData);

        foreach ($globalProductData['skus'] ?? [] as $skuData) {
            $this->handleGlobalVariantSkuId($globalProduct, $skuData);
        }
    }

    private function isNeedHandle(
        \M2E\TikTokShop\Model\Product $product,
        array $globalProductData
    ): bool {
        if (!$product->isGlobalProduct()) {
            return false;
        }

        if (empty($globalProductData)) {
            return false;
        }

        return true;
    }

    private function handleGlobalProductId(
        \M2E\TikTokShop\Model\GlobalProduct $globalProduct,
        array $globalProductData
    ): void {
        if (empty($globalProductData['id'])) {
            return;
        }

        if ($globalProduct->getGlobalId() !== null) {
            return;
        }

        $globalProduct->setGlobalId($globalProductData['id']);
        $this->globalProductRepository->save($globalProduct);
    }

    private function handleGlobalVariantSkuId(
        \M2E\TikTokShop\Model\GlobalProduct $globalProduct,
        array $skuData
    ): void {
        $globalId = $skuData['id'] ?? null;
        $sellerSku = $skuData['seller_sku'] ?? null;

        if (empty($globalId) || empty($sellerSku)) {
            return;
        }

        $globalVariantSku = $globalProduct->findGlobalVariantBySku($sellerSku);
        if ($globalVariantSku === null) {
            return;
        }

        if ($globalVariantSku->getGlobalId() !== null) {
            return;
        }

        $globalVariantSku->setGlobalId($skuData['id']);
        $this->globalProductRepository->saveVariantSku($globalVariantSku);
    }
}
