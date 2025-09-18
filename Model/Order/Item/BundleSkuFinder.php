<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

class BundleSkuFinder
{
    private \M2E\TikTokShop\Model\Magento\Product $magentoProduct;
    private CombinedListingSkus $combinedListingSkus;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;
    private \M2E\TikTokShop\Model\Magento\Product\BundleService $bundleService;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct,
        CombinedListingSkus $combinedListingSkus,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \M2E\TikTokShop\Model\Magento\Product\BundleService $bundleService
    ) {
        $this->magentoProduct = $magentoProduct;
        $this->combinedListingSkus = $combinedListingSkus;
        $this->magentoProductHelper = $magentoProductHelper;
        $this->bundleService = $bundleService;
    }

    public function find(): ?array
    {
        if (!$this->magentoProductHelper->isBundleType($this->magentoProduct->getTypeId())) {
            return null;
        }

        $bundleSelectionsGroupedBySku = $this->bundleService
            ->getSelectionsGroupBySku($this->magentoProduct);

        $associatedOptions = [];
        $associatedProducts = [];

        foreach ($this->combinedListingSkus->getList() as $combinedListingSku) {
            $productSku = $combinedListingSku->sellerSku;
            if (!isset($bundleSelectionsGroupedBySku[$productSku])) {
                continue;
            }

            foreach ($bundleSelectionsGroupedBySku[$productSku] as $bundleSelection) {
                $associatedOptions[$bundleSelection->getOption()->getOptionId()][] = $bundleSelection->getSelectionId();
                $associatedProducts[$bundleSelection->getOptionIdSelectionIdKey()] = $bundleSelection->getProductId();
            }
        }

        return [
            'associated_options' => $associatedOptions,
            'associated_products' => $associatedProducts,
        ];
    }
}
