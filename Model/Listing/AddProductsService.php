<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

use M2E\TikTokShop\Model\Product;

class AddProductsService
{
    private Product\Repository $listingProductRepository;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedProductRepository;
    /** @var \M2E\TikTokShop\Model\Product\CreateService */
    private Product\CreateService $createProductService;

    public function __construct(
        \M2E\TikTokShop\Model\Product\CreateService $createProductService,
        Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedProductRepository,
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService
    ) {
        $this->listingProductRepository = $listingProductRepository;
        $this->instructionService = $instructionService;
        $this->listingLogService = $listingLogService;
        $this->unmanagedProductRepository = $unmanagedProductRepository;
        $this->createProductService = $createProductService;
    }

    public function addProduct(
        \M2E\TikTokShop\Model\Listing $listing,
        \M2E\TikTokShop\Model\Magento\Product $ourMagentoProduct,
        int $categoryDictionaryId,
        int $initiator,
        ?\M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct = null
    ): ?Product {
        if (!$ourMagentoProduct->exists()) {
            throw new \M2E\TikTokShop\Model\Listing\Exception\MagentoProductNotFoundException(
                'Magento product not found.',
                ['magento_product_id' => $ourMagentoProduct->getProductId()]
            );
        }

        $listingProduct = $this->findExistProduct($listing, $ourMagentoProduct->getProductId());
        if ($listingProduct !== null) {
            return null;
        }

        if (!$listing->getShop()->hasDefaultWarehouse()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Product cannot be added because the default warehouse was not found.');
        }

        $listingProduct = $this->createProductService->create(
            $listing,
            $ourMagentoProduct,
            $categoryDictionaryId,
            $listing->getShop()->getDefaultWarehouse()->getId(),
            $unmanagedProduct,
        );

        $logMessage = (string)__('Product was Added');
        $logAction = \M2E\TikTokShop\Model\Listing\Log::ACTION_ADD_PRODUCT_TO_LISTING;

        if (!empty($unmanagedProduct)) {
            $logMessage = (string)__('Item was Moved');
            $logAction = \M2E\TikTokShop\Model\Listing\Log::ACTION_MOVE_FROM_OTHER_LISTING;
        }

        // Add message for listing log
        // ---------------------------------------
        $this->listingLogService->addProduct(
            $listingProduct,
            $initiator,
            $logAction,
            null,
            $logMessage,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );
        // ---------------------------------------

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_ADDED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
            70,
            \M2E\TikTokShop\Helper\Date::createCurrentGmt()->modify('+1 minutes')
        );

        return $listingProduct;
    }

    public function addFromUnmanaged(
        \M2E\TikTokShop\Model\Listing $listing,
        \M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct,
        int $categoryDictionaryId,
        int $initiator
    ): ?Product {
        if (!$unmanagedProduct->hasMagentoProductId()) {
            return null;
        }

        if (!$unmanagedProduct->isListingCorrectForMove($listing)) {
            return null;
        }

        $existProduct = $this->listingProductRepository->findByTtsProductIds(
            [$unmanagedProduct->getProductId()],
            $unmanagedProduct->getAccountId(),
            $unmanagedProduct->getShopId(),
            $listing->getId(),
        );
        if (!empty($existProduct)) {
            return null;
        }

        $magentoProduct = $unmanagedProduct->getMagentoProduct();

        $listingProduct = $this->addProduct(
            $listing,
            $magentoProduct,
            $categoryDictionaryId,
            $initiator,
            $unmanagedProduct,
        );
        if ($listingProduct === null) {
            return null;
        }

        $unmanagedProduct->setMovedToListingProductId($listingProduct->getId());
        $this->unmanagedProductRepository->save($unmanagedProduct);

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            20,
        );

        return $listingProduct;
    }

    private function findExistProduct(\M2E\TikTokShop\Model\Listing $listing, int $magentoProductId): ?Product
    {
        return $this->listingProductRepository->findByListingAndMagentoProductId($listing, $magentoProductId);
    }

    /**
     * @param \M2E\TikTokShop\Model\Product $listingProduct
     * @param \M2E\TikTokShop\Model\Listing $targetListing
     * @param \M2E\TikTokShop\Model\Listing $sourceListing
     *
     * @return bool
     * @throws \Exception
     */
    public function addProductFromListing(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Listing $targetListing,
        \M2E\TikTokShop\Model\Listing $sourceListing
    ) {
        if ($this->findExistProduct($targetListing, $listingProduct->getMagentoProductId()) !== null) {
            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\TikTokShop\Helper\Data::INITIATOR_USER,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
                null,
                (string)__('The Product was not moved because it already exists in the selected Listing'),
                \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
            );

            return false;
        }

        $listingProduct->changeListing($targetListing);
        $this->listingProductRepository->save($listingProduct);

        $logMessage = (string)__(
            'Item was moved from Listing %previous_listing_name.',
            [
                'previous_listing_name' => $sourceListing->getTitle()
            ],
        );

        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\TikTokShop\Helper\Data::INITIATOR_USER,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
            null,
            $logMessage,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );

        $logMessage = (string)__(
            'Product %product_title was moved to Listing %current_listing_name',
            [
                'product_title' => $listingProduct->getMagentoProduct()->getName(),
                'current_listing_name' => $targetListing->getTitle(),
            ],
        );

        $this->listingLogService->addListing(
            $sourceListing,
            \M2E\TikTokShop\Helper\Data::INITIATOR_USER,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
            null,
            $logMessage,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING,
            20
        );

        return true;
    }
}
