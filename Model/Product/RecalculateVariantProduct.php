<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class RecalculateVariantProduct
{
    private const INSTRUCTION_INITIATOR_UNASSIGN_VARIANT_FROM_MAGENTO = 'unassign_variant_from_magento';
    private const INSTRUCTION_INITIATOR_ASSIGN_VARIANT_FROM_MAGENTO = 'assign_variant_from_magento';

    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private \M2E\TikTokShop\Model\Product\VariantSkuFactory $variantSkuFactory;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private int $actionId;

    public function __construct(
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Product\VariantSkuFactory $variantSkuFactory,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository
    ) {
        $this->instructionService = $instructionService;
        $this->productRepository = $productRepository;
        $this->listingLogService = $listingLogService;
        $this->variantSkuFactory = $variantSkuFactory;
        $this->listingRepository = $listingRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Product $magentoProduct
     * @param \M2E\TikTokShop\Model\Product\AffectedProduct\Product[] $affectedProducts
     *
     * @return void
     */
    public function process(
        \Magento\Catalog\Model\Product $magentoProduct,
        array $affectedProducts
    ): void {
        if (empty($magentoProduct->getTypeInstance()->getUsedProducts($magentoProduct))) {
            return;
        }

        $this->actionId = $this->listingLogService->getNextActionId();
        $magentoVariations = [];
        foreach ($magentoProduct->getTypeInstance()->getUsedProducts($magentoProduct) as $variation) {
            $magentoVariations[$variation->getEntityId()] = $variation;
        }

        foreach ($affectedProducts as $affectedProduct) {
            $ttsProduct = $affectedProduct->getProduct();
            if (empty($ttsProduct->getVariants())) {
                continue;
            }

            $ttsProductVariants = $this->processTtsProductVariants($ttsProduct, $magentoVariations);
            $this->processMagentoProductVariants($ttsProduct, $magentoVariations, $ttsProductVariants);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $magentoProduct
     * @param array<integer, \Magento\Catalog\Model\Product> $magentoVariations
     * @param array<integer, \M2E\TikTokShop\Model\Product\VariantSku> $ttsProductVariants
     *
     * @return void
     */
    private function processMagentoProductVariants(
        \M2E\TikTokShop\Model\Product $ttsProduct,
        array $magentoVariations,
        array $ttsProductVariants
    ): void {
        foreach ($magentoVariations as $magentoVariation) {
            $magentoVariationId = (int)$magentoVariation->getId();
            if (!isset($ttsProductVariants[$magentoVariationId])) {
                $this->assignVariant($ttsProduct, $magentoVariationId);
            }
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\Product $ttsProduct
     * @param array<integer, \Magento\Catalog\Model\Product> $magentoVariations
     *
     * @return array<integer, \M2E\TikTokShop\Model\Product\VariantSku>
     */
    private function processTtsProductVariants(
        \M2E\TikTokShop\Model\Product $ttsProduct,
        array $magentoVariations
    ): array {
        $ttsProductVariants = [];
        foreach ($ttsProduct->getVariants() as $ttsVariant) {
            $ttsProductVariants[$ttsVariant->getMagentoProductId()] = $ttsVariant;
            if (!isset($magentoVariations[$ttsVariant->getMagentoProductId()])) {
                $this->unassignVariant($ttsProduct, $ttsVariant);
            }
        }
        return $ttsProductVariants;
    }

    private function unassignVariant(
        \M2E\TikTokShop\Model\Product $ttsParentProduct,
        \M2E\TikTokShop\Model\Product\VariantSku $ttsVariant
    ): void {
        $this->productRepository->deleteVariantSku($ttsVariant);

        if ($ttsParentProduct->isStatusListed()) {
            $this->instructionService->create(
                $ttsParentProduct->getId(),
                \M2E\TikTokShop\Model\Product::INSTRUCTION_TYPE_VARIANT_SKU_REMOVED,
                self::INSTRUCTION_INITIATOR_UNASSIGN_VARIANT_FROM_MAGENTO,
                80,
            );
        }

        $this->addUnassignVariantLog($ttsVariant);
    }

    private function assignVariant(\M2E\TikTokShop\Model\Product $ttsParentProduct, int $magentoProductId): void
    {
        $listing = $this->listingRepository->get($ttsParentProduct->getListingId());

        if (!$listing->getShop()->hasDefaultWarehouse()) {
            return;
        }

        $variantSku = $this->variantSkuFactory->create();
        $variantSku->init($ttsParentProduct, $magentoProductId);
        $this->productRepository->saveVariantSku($variantSku);

        if ($ttsParentProduct->isStatusListed()) {
            $this->instructionService->create(
                $ttsParentProduct->getId(),
                \M2E\TikTokShop\Model\Product::INSTRUCTION_TYPE_VARIANT_SKU_ADDED,
                self::INSTRUCTION_INITIATOR_ASSIGN_VARIANT_FROM_MAGENTO,
                80,
            );
        }

        $this->addAssignVariantLog($variantSku);
    }

    private function addAssignVariantLog(\M2E\TikTokShop\Model\Product\VariantSku $variantSku): void
    {
        $message = (string)__(
            'SKU %sku: The variation was added to the product',
            ['sku' => $variantSku->getSku()]
        );

        $this->addLog(
            $variantSku,
            $message,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_ADD_PRODUCT_TO_LISTING
        );
    }

    private function addUnassignVariantLog(\M2E\TikTokShop\Model\Product\VariantSku $variantSku): void
    {
        $message = (string)__(
            'SKU %sku: The variation was removed from the product.',
            ['sku' => $variantSku->getSku()]
        );

        $this->addLog(
            $variantSku,
            $message,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_LISTING
        );
    }

    private function addLog(
        \M2E\TikTokShop\Model\Product\VariantSku $variantSku,
        string $message,
        int $action
    ): void {
        $this->listingLogService->addProduct(
            $variantSku->getProduct(),
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            $action,
            $this->actionId,
            $message,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_WARNING,
        );
    }
}
