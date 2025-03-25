<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct;

use M2E\TikTokShop\Model\GlobalProduct\Move\Result as MoveResult;

class Move
{
    private \M2E\TikTokShop\Model\GlobalProduct\Move\Validator $validator;
    private \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository;
    private \M2E\TikTokShop\Model\GlobalProduct\CreateFromProduct $globalProductCreator;
    private \M2E\TikTokShop\Model\Listing\AddProductsService $addProductToListing;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository;
    private \M2E\TikTokShop\Model\Category\CopyToOtherShop $copyCategoryToOtherShop;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\TikTokShop\Model\GlobalProduct\Move\Validator $validator,
        \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository,
        \M2E\TikTokShop\Model\GlobalProduct\CreateFromProduct $globalProductCreator,
        \M2E\TikTokShop\Model\Listing\AddProductsService $addProductToListing,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository,
        \M2E\TikTokShop\Model\Category\CopyToOtherShop $duplicateToOtherShop,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper
    ) {
        $this->validator = $validator;
        $this->globalProductRepository = $globalProductRepository;
        $this->globalProductCreator = $globalProductCreator;
        $this->addProductToListing = $addProductToListing;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->copyCategoryToOtherShop = $duplicateToOtherShop;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function execute(
        \M2E\TikTokShop\Model\Listing $sourceListing,
        \M2E\TikTokShop\Model\Listing $targetListing,
        \M2E\TikTokShop\Model\Product $product
    ): MoveResult {
        if (!$this->validator->isProductListed($product)) {
            return MoveResult::createFail(['Product Not Listed']);
        }

        if ($this->validator->isDifferentAccount($sourceListing, $targetListing)) {
            return MoveResult::createFail(['Different Accounts']);
        }

        if ($this->validator->isSameShop($sourceListing, $targetListing)) {
            return MoveResult::createFail(['Same Shop']);
        }

        $globalProduct = $this->findOrCreateGlobalProduct($product);

        $addedProduct = $this->addProductToListing($product, $globalProduct, $targetListing);
        if ($addedProduct === null) {
            return MoveResult::createFail(['Product Existed in Listing']);
        }

        $addedProduct = $this->tryMapCategory($addedProduct);
        $addedProduct = $this->tryCopyCategory($addedProduct, $globalProduct);
        $addedProduct->setGlobalProductId($globalProduct->getId());
        $this->productRepository->save($addedProduct);

        return MoveResult::createSuccess();
    }

    private function findOrCreateGlobalProduct(\M2E\TikTokShop\Model\Product $product): \M2E\TikTokShop\Model\GlobalProduct
    {
        $globalProduct = $this->findGlobalProduct($product);

        if ($globalProduct !== null) {
            return $globalProduct;
        }

        return $this->globalProductCreator->execute($product);
    }

    private function findGlobalProduct(\M2E\TikTokShop\Model\Product $product): ?\M2E\TikTokShop\Model\GlobalProduct
    {
        return $this->globalProductRepository
            ->findByAccountIdAndMagentoProductId(
                $product->getAccount()->getId(),
                $product->getMagentoProductId()
            );
    }

    private function addProductToListing(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\GlobalProduct $globalProduct,
        \M2E\TikTokShop\Model\Listing $targetListing
    ): ?\M2E\TikTokShop\Model\Product {
        return $this->addProductToListing->addProduct(
            $targetListing,
            $product->getMagentoProduct(),
            $globalProduct->getSourceProduct()->getTemplateCategoryId(),
            \M2E\TikTokShop\Helper\Data::INITIATOR_USER
        );
    }

    private function tryMapCategory(\M2E\TikTokShop\Model\Product $addedProduct): \M2E\TikTokShop\Model\Product
    {
        $newCategory = $this->categoryRepository->findByShopAndCategoryId(
            $addedProduct->getShop()->getId(),
            $addedProduct->getCategoryDictionary()->getCategoryId(),
        );

        if ($newCategory === null) {
            $addedProduct->removeTemplateCategoryId();

            return $addedProduct;
        }

        $addedProduct->setTemplateCategoryId($newCategory->getId());

        return $addedProduct;
    }

    private function tryCopyCategory(
        \M2E\TikTokShop\Model\Product $addedProduct,
        \M2E\TikTokShop\Model\GlobalProduct $globalProduct
    ): \M2E\TikTokShop\Model\Product {
        if ($addedProduct->hasCategoryTemplate()) {
            return $addedProduct;
        }

        try {
            $categoryDictionary = $globalProduct->getSourceProduct()->getCategoryDictionary();
            $shop = $addedProduct->getShop();

            $copiedDictionary = $this->copyCategoryToOtherShop
                ->execute($categoryDictionary, $shop);
        } catch (\Throwable $exception) {
            $this->exceptionHelper->process($exception);

            return $addedProduct;
        }

        $addedProduct->setTemplateCategoryId($copiedDictionary->getId());

        return $addedProduct;
    }
}
