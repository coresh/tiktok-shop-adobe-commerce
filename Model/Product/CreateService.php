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
        ?int $warehouseId,
        ?\M2E\TikTokShop\Model\Listing\Other $unmanagedProduct = null
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

        $this->createVariants($warehouseId, $listingProduct, $m2eMagentoProduct, $unmanagedProduct);

        return $listingProduct;
    }

    private function createVariants(
        ?int $warehouseId,
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Magento\Product $m2eMagentoProduct,
        $unmanagedProduct = null
    ): void {
        $variants = [];
        if ($m2eMagentoProduct->isSimpleType()) {
            $variants[] = $this->createVariantEntity(
                $listingProduct,
                $m2eMagentoProduct,
                $warehouseId,
                $unmanagedProduct,
            );
        } else {
            if ($m2eMagentoProduct->isGroupedType()) {
                foreach ($m2eMagentoProduct->getGroupedChildren() as $child) {
                    $variants[] = $this->createVariantEntity($listingProduct, $child, $warehouseId, $unmanagedProduct);
                }
            }

            if ($m2eMagentoProduct->isConfigurableType()) {
                foreach ($m2eMagentoProduct->getConfigurableChildren() as $child) {
                    $variants[] = $this->createVariantEntity($listingProduct, $child, $warehouseId, $unmanagedProduct);
                }
            }
        }

        $this->listingProductRepository->createVariantsSku($variants);
    }

    private function createVariantEntity(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Magento\Product $m2eMagentoProduct,
        ?int $warehouseId,
        $unmanagedProduct = null
    ): VariantSku {
        $variantSku = $this->variantSkuFactory->create();
        $variantSku->init($listingProduct, $m2eMagentoProduct->getProductId(), $warehouseId);
        if ($unmanagedProduct !== null) {
            $variantSku->fillFromUnmanagedProduct($unmanagedProduct);
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
