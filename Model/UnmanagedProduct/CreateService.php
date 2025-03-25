<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct;

class CreateService
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\VariantSkuFactory $variantFactory;
    private \M2E\TikTokShop\Model\UnmanagedProductFactory $productFactory;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttributeFactory $salesAttributeFactory;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttributeFactory $salesAttributeFactory,
        \M2E\TikTokShop\Model\UnmanagedProduct\VariantSkuFactory $variantFactory,
        \M2E\TikTokShop\Model\UnmanagedProductFactory $productFactory,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository
    ) {
        $this->salesAttributeFactory = $salesAttributeFactory;
        $this->variantFactory = $variantFactory;
        $this->productFactory = $productFactory;
        $this->unmanagedRepository = $unmanagedRepository;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function create(
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\Product $ttsProduct
    ): \M2E\TikTokShop\Model\UnmanagedProduct {
        $unmanagedProduct = $this->productFactory->create();
        $unmanagedProduct->init(
            $ttsProduct->getAccountId(),
            $ttsProduct->getShopId(),
            $ttsProduct->getProductId(),
            $ttsProduct->getStatus(),
            $ttsProduct->getTitle(),
            $ttsProduct->getCategoryId(),
            $ttsProduct->getCategoriesData(),
            $ttsProduct->isNotForSale()
        );

        $this->unmanagedRepository->create($unmanagedProduct);

        $this->createVariants($unmanagedProduct, $ttsProduct->getVariantCollection());

        $unmanagedProduct->calculateDataByVariants();
        $this->unmanagedRepository->save($unmanagedProduct);

        return $unmanagedProduct;
    }

    private function createVariants(
        \M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSkuCollection $ttsProductSkuCollection
    ): void {
        $variants = [];
        foreach ($ttsProductSkuCollection->getAll() as $productSku) {
            $variants[] = $this->createVariantEntity($unmanagedProduct, $productSku);
        }
        $this->unmanagedRepository->saveVariants($variants);
    }

    private function createVariantEntity(
        \M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku $ttsProductSku
    ): \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku {
        $warehouse = $this->warehouseRepository->findByWarehouseId($ttsProductSku->getWarehouseId());
        $variant = $this->variantFactory->create();

        $variant->init(
            $unmanagedProduct,
            $warehouse,
            $ttsProductSku->getStatus(),
            $ttsProductSku->getSkuId(),
            $ttsProductSku->getSku(),
            $ttsProductSku->getQty(),
            $ttsProductSku->getPrice(),
            $ttsProductSku->getCurrency(),
            $ttsProductSku->getIdentifier(),
            $this->createSalesAttributes($ttsProductSku)
        );

        return $variant;
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku $ttsProductSku
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku\SalesAttribute[]
     */
    private function createSalesAttributes(
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku $ttsProductSku
    ): array {
        $salesAttributes = [];
        foreach ($ttsProductSku->getSalesAttributes() as $salesAttribute) {
            $salesAttributes[] = $this->salesAttributeFactory->create($salesAttribute);
        }
        return $salesAttributes;
    }
}
