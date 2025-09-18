<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Wizard;

class CompleteProcessor
{
    private \M2E\TikTokShop\Model\Listing\AddProductsService $addProductsService;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService;
    private \M2E\TikTokShop\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\TikTokShop\Model\Product\Category\Attribute\ValidateManager $productAttributeValidateManager;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\AddProductsService $addProductsService,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService,
        \M2E\TikTokShop\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\TikTokShop\Model\Product\Category\Attribute\ValidateManager $productAttributeValidateManager
    ) {
        $this->addProductsService = $addProductsService;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->productAttributeValidateManager = $productAttributeValidateManager;
    }

    public function process(Manager $wizardManager): array
    {
        $listing = $wizardManager->getListing();

        $processedWizardProductIds = [];
        $listingProducts = [];
        foreach ($wizardManager->getNotProcessedProducts() as $wizardProduct) {
            $listingProduct = null;

            $processedWizardProductIds[] = $wizardProduct->getId();

            $magentoProduct = $this->magentoProductFactory->create()->setProductId($wizardProduct->getMagentoProductId());
            if (!$magentoProduct->exists()) {
                continue;
            }

            if ($wizardManager->isWizardTypeGeneral()) {
                $listingProduct = $this->addProductsService
                    ->addProduct(
                        $listing,
                        $magentoProduct,
                        $wizardProduct->getCategoryDictionaryId(),
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );
            } elseif ($wizardManager->isWizardTypeUnmanaged()) {
                $unmanagedProduct = $this->listingOtherRepository->findById($wizardProduct->getUnmanagedProductId());
                if ($unmanagedProduct === null) {
                    continue;
                }

                if (!$unmanagedProduct->getMagentoProduct()->exists()) {
                    continue;
                }

                $listingProduct = $this->addProductsService
                    ->addFromUnmanaged(
                        $listing,
                        $unmanagedProduct,
                        $wizardProduct->getCategoryDictionaryId(),
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );

                $this->unmanagedProductDeleteService->process($unmanagedProduct);
            }

            if ($listingProduct === null) {
                continue;
            }

            if ($wizardProduct->isInvalidCategoryAttributes()) {
                $this->productAttributeValidateManager->markProductAsNotValid(
                    $listingProduct,
                    $wizardProduct->getCategoryAttributesErrors()
                );
            } else {
                $this->productAttributeValidateManager->markProductAsValid($listingProduct);
            }

            $listingProducts[] = $listingProduct;

            if (count($processedWizardProductIds) % 100 === 0) {
                $wizardManager->markProductsAsProcessed($processedWizardProductIds);
                $processedWizardProductIds = [];
            }
        }

        if (!empty($processedWizardProductIds)) {
            $wizardManager->markProductsAsProcessed($processedWizardProductIds);
        }

        return $listingProducts;
    }
}
