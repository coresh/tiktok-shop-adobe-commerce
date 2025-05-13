<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class CreateService
{
    private \M2E\TikTokShop\Model\ProductFactory $listingProductFactory;
    /** @var \M2E\TikTokShop\Model\Product\Repository */
    private Repository $listingProductRepository;
    /** @var \M2E\TikTokShop\Model\Product\VariantSkuFactory */
    private VariantSkuFactory $variantSkuFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ProductFactory $listingProductFactory,
        Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Product\VariantSkuFactory $variantSkuFactory
    ) {
        $this->listingProductFactory = $listingProductFactory;
        $this->listingProductRepository = $listingProductRepository;
        $this->variantSkuFactory = $variantSkuFactory;
    }

    public function create(
        \M2E\TikTokShop\Model\Listing $listing,
        \M2E\TikTokShop\Model\Magento\Product $m2eMagentoProduct,
        int $categoryDictionaryId,
        ?\M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct = null
    ): \M2E\TikTokShop\Model\Product {
        $this->checkSupportedMagentoType($m2eMagentoProduct);

        $listingProduct = $this->listingProductFactory->create();
        $listingProduct->init(
            $listing,
            $m2eMagentoProduct->getProductId(),
            $m2eMagentoProduct->isSimpleType(),
            $categoryDictionaryId,
        );

        if ($unmanagedProduct !== null) {
            $listingProduct->fillFromUnmanagedProduct($unmanagedProduct);
        }

        $this->listingProductRepository->create($listingProduct);
        $variants = $this->createVariants($listingProduct, $m2eMagentoProduct, $unmanagedProduct);

        $this->listingProductRepository->createVariantsSku($variants);
        $this->listingProductRepository->save($listingProduct->recalculateOnlineDataByVariants());

        return $listingProduct;
    }

    private function createVariants(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Magento\Product $m2eMagentoProduct,
        ?\M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct
    ): array {
        $unmanagedVariants = [];
        if ($unmanagedProduct !== null) {
            foreach ($unmanagedProduct->getVariants() as $variant) {
                if ($variant->hasMagentoProductId()) {
                    $unmanagedVariants[$variant->getMagentoProductId()] = $variant;
                }
            }
        }

        if ($m2eMagentoProduct->isSimpleType()) {
            return [
                $this->createVariantEntity(
                    $listingProduct,
                    $m2eMagentoProduct,
                    $unmanagedVariants[$m2eMagentoProduct->getProductId()] ?? null
                ),
            ];
        }

        $variants = [];
        foreach ($m2eMagentoProduct->getConfigurableChildren() as $child) {
            $variants[] = $this->createVariantEntity(
                $listingProduct,
                $child,
                $unmanagedVariants[$child->getProductId()] ?? null
            );
        }

        return $variants;
    }

    private function createVariantEntity(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Magento\Product $m2eMagentoProduct,
        ?\M2E\TikTokShop\Model\UnmanagedProduct\VariantSku $variant = null
    ): VariantSku {
        $variantSku = $this->variantSkuFactory->create();
        $variantSku->init($listingProduct, $m2eMagentoProduct->getProductId());

        if ($variant !== null) {
            $variantSku->fillFromUnmanagedVariant($variant);
        }

        return $variantSku;
    }

    // ----------------------------------------

    private function checkSupportedMagentoType(\M2E\TikTokShop\Model\Magento\Product $m2eMagentoProduct): void
    {
        if (!$this->isSupportedMagentoProductType($m2eMagentoProduct)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                (string)__(
                    sprintf('Unsupported magento product type - %s', $m2eMagentoProduct->getTypeId()),
                ),
            );
        }
    }

    private function isSupportedMagentoProductType(\M2E\TikTokShop\Model\Magento\Product $ourMagentoProduct): bool
    {
        return $ourMagentoProduct->isSimpleType()
            || $ourMagentoProduct->isConfigurableType();
    }
}
