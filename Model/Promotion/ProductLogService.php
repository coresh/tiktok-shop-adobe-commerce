<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

class ProductLogService
{
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        $this->listingLogService = $listingLogService;
        $this->productRepository = $productRepository;
    }

    public function addLogForAllProductsAboutRemoveFromPromotion(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $this->addLogAboutRemove($promotion, []);
    }

    /**
     * @param \M2E\TikTokShop\Model\Promotion $promotion
     * @param \M2E\TikTokShop\Model\Promotion\Product[] $removeProducts
     *
     * @return void
     */
    public function addLogForRemovedProductsAboutRemoveFromPromotion(
        \M2E\TikTokShop\Model\Promotion $promotion,
        array $removeProducts
    ): void {
        $this->addLogAboutRemove($promotion, $removeProducts);
    }

    /**
     * @param \M2E\TikTokShop\Model\Promotion $promotion
     * @param \M2E\TikTokShop\Model\Promotion\Product[] $removeProducts
     *
     * @return void
     */
    private function addLogAboutRemove(\M2E\TikTokShop\Model\Promotion $promotion, array $removeProducts): void
    {
        $removeProductIds = array_map(static function (\M2E\TikTokShop\Model\Promotion\Product $product) {
            return $product->getProductId();
        }, $removeProducts);

        if ($promotion->isProductLevelByProduct()) {
            foreach ($this->productRepository->findProductsByPromotion($promotion, $removeProductIds) as $product) {
                $this->addRemoveFromPromotion($product);
            }
        } elseif ($promotion->isProductLevelByVariation()) {
            $existPromotionSkus = [];
            $removeProductSkuIds = [];
            foreach ($removeProducts as $removeProduct) {
                $existPromotionSkus[$removeProduct->getSkuId()] = true;
                $removeProductSkuIds[] = $removeProduct->getSkuId();
            }

            foreach ($this->productRepository->findProductsWithVariantOnPromotionByPromotion($promotion, $removeProductSkuIds) as $product) {
                foreach ($product->getVariants() as $variant) {
                    if (!isset($existPromotionSkus[$variant->getSkuId()])) {
                        continue;
                    }

                    $this->addRemoveVariantFromPromotion($product, $variant->getSku());
                }
            }
        }
    }

    private function addRemoveFromPromotion(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->addLog($product, (string)__('The Item is no longer a part of the promotion'));
    }

    private function addRemoveVariantFromPromotion(\M2E\TikTokShop\Model\Product $product, string $variantSku): void
    {
        $this->addLog(
            $product,
            (string)__(
                'SKU "%sku": The Item is no longer a part of the promotion.',
                ['sku' => $variantSku]
            )
        );
    }

    public function addLogForAllProductsAboutAddToPromotion(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $this->addLogAboutAddTo($promotion, []);
    }

    /**
     * @param \M2E\TikTokShop\Model\Promotion $promotion
     * @param \M2E\TikTokShop\Model\Promotion\Product[] $addedProducts
     *
     * @return void
     */
    public function addLogForAddedProductsAboutAddToPromotion(
        \M2E\TikTokShop\Model\Promotion $promotion,
        array $addedProducts
    ): void {
        $this->addLogAboutAddTo($promotion, $addedProducts);
    }

    /**
     * @param \M2E\TikTokShop\Model\Promotion $promotion
     * @param \M2E\TikTokShop\Model\Promotion\Product[] $addedProducts
     *
     * @return void
     */
    private function addLogAboutAddTo(\M2E\TikTokShop\Model\Promotion $promotion, array $addedProducts): void
    {
        if ($promotion->isProductLevelByProduct()) {
            $addedChannelProductId = array_map(static function (\M2E\TikTokShop\Model\Promotion\Product $product) {
                return $product->getProductId();
            }, $addedProducts);

            foreach ($this->productRepository->findProductsByPromotion($promotion, $addedChannelProductId) as $product) {
                $this->addAddToPromotion($product);
            }
        } elseif ($promotion->isProductLevelByVariation()) {
            $existPromotionSkus = [];
            $addedChannelProductSkuIds = [];

            if (!empty($addedProducts)) {
                foreach ($addedProducts as $addedProduct) {
                    $existPromotionSkus[$addedProduct->getSkuId()] = true;
                    $addedChannelProductSkuIds[] = $addedProduct->getSkuId();
                }
            } else {
                foreach ($promotion->getSkus() as $sku) {
                    $existPromotionSkus[$sku->getSkuId()] = true;
                }
            }

            foreach ($this->productRepository->findProductsWithVariantOnPromotionByPromotion($promotion, $addedChannelProductSkuIds) as $product) {
                foreach ($product->getVariants() as $variant) {
                    if (!isset($existPromotionSkus[$variant->getSkuId()])) {
                        continue;
                    }

                    $this->addAddVariantToPromotion($product, $variant->getSku());
                }
            }
        }
    }

    private function addAddToPromotion(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->addLog($product, (string)__('The Item was added to the promotion'));
    }

    private function addAddVariantToPromotion(\M2E\TikTokShop\Model\Product $product, string $variantSku): void
    {
        $this->addLog(
            $product,
            (string)__(
                'SKU "%sku": The Item was added to the promotion.',
                ['sku' => $variantSku]
            )
        );
    }

    private function addLog(\M2E\TikTokShop\Model\Product $product, string $message): void
    {
        $this->listingLogService->addProduct(
            $product,
            \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_PROMOTION,
            null,
            $message,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
