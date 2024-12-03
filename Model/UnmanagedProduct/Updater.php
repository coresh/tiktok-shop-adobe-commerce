<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct;

use M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductCollection as ChannelProductCollection;

class Updater
{
    private Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService;
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $unmanagedDeleteService;
    private \M2E\TikTokShop\Model\UnmanagedProduct\CreateService $unmanagedCreateService;
    /** @var array<string, \M2E\TikTokShop\Model\Warehouse> */
    private array $warehousesByTtsId;

    public function __construct(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService,
        \M2E\TikTokShop\Model\UnmanagedProduct\CreateService $unmanagedCreateService,
        \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $unmanagedDeleteService
    ) {
        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->listingProductRepository = $listingProductRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->account = $account;
        $this->shop = $shop;
        $this->unmanagedCreateService = $unmanagedCreateService;
        $this->unmanagedDeleteService = $unmanagedDeleteService;
    }

    public function process(ChannelProductCollection $channelProductCollection): ?ChannelProductCollection
    {
        if ($channelProductCollection->empty()) {
            return null;
        }

        $this->removeWithUnknownWarehouse($channelProductCollection);
        $existInListingCollection = $this->removeExistInListingProduct($channelProductCollection);

        $this->processExist($channelProductCollection);
        $unmanagedItems = $this->processNew($channelProductCollection);

        // remove not exist

        $this->autoMapping($unmanagedItems);

        return $existInListingCollection;
    }

    private function removeWithUnknownWarehouse(ChannelProductCollection $channelProductCollection): void
    {
        foreach ($channelProductCollection->getAll() as $channelProduct) {
            foreach ($channelProduct->getVariantCollection()->getAll() as $variant) {
                if ($this->findWarehouse($variant->getWarehouseId()) === null) {
                    $channelProductCollection->remove($channelProduct->getProductId());

                    break;
                }
            }
        }
    }

    private function removeExistInListingProduct(ChannelProductCollection $channelProductCollection): ChannelProductCollection
    {
        $existInListingCollection = new ChannelProductCollection();
        if ($channelProductCollection->empty()) {
            return $existInListingCollection;
        }

        $existed = $this->listingProductRepository->findByTtsProductIds(
            $channelProductCollection->getProductsIds(),
            $this->account->getId(),
            $this->shop->getId()
        );

        foreach ($existed as $product) {
            $existInListingCollection->add($channelProductCollection->get($product->getTTSProductId()));

            $channelProductCollection->remove($product->getTTSProductId());
        }

        return $existInListingCollection;
    }

    private function processExist(ChannelProductCollection $channelProductCollection): void
    {
        if ($channelProductCollection->empty()) {
            return;
        }

        $existProducts = $this->unmanagedRepository->findByProductIds(
            $channelProductCollection->getProductsIds(),
            $this->account->getId(),
            $this->shop->getId(),
        );

        foreach ($existProducts as $existProduct) {
            if (!$channelProductCollection->has($existProduct->getProductId())) {
                continue;
            }

            $new = $channelProductCollection->get($existProduct->getProductId());

            $channelProductCollection->remove($existProduct->getProductId());

            // removed
            if ($new->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_BLOCKED) {
                $this->unmanagedDeleteService->process($existProduct);

                continue;
            }

            if ($new->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                $this->unmanagedDeleteService->process($existProduct);

                continue;
            }

            if ($existProduct->getTitle() !== $new->getTitle()) {
                $existProduct->setTitle($new->getTitle());
            }

            if ($existProduct->getStatus() !== $new->getStatus()) {
                $existProduct->setStatus($new->getStatus());
            }

            $existProductChanged = false;
            foreach ($existProduct->getVariants() as $existVariant) {
                $variantCollection = $new->getVariantCollection();

                $newVariantSku = $variantCollection->findProductSkuBySkuId($existVariant->getSkuId());

                if (!$newVariantSku) {
                    continue;
                }

                $existingVariantChanged = false;

                if ($existVariant->getQty() !== $newVariantSku->getQty()) {
                    $existVariant->setQty($newVariantSku->getQty());
                    $existingVariantChanged = true;
                }

                if ($existVariant->getCurrentPrice() !== $newVariantSku->getPrice()) {
                    $existVariant->setCurrentPrice($newVariantSku->getPrice());
                    $existingVariantChanged = true;
                }

                if ($existVariant->getSku() !== $newVariantSku->getSku()) {
                    $existVariant->setSku($newVariantSku->getSku());
                    $existingVariantChanged = true;
                }

                if ($existingVariantChanged) {
                    $this->unmanagedRepository->saveVariant($existVariant);
                    $existProductChanged = true;
                }
            }

            if ($existProductChanged) {
                $existProduct->calculateDataByVariants();
            }

            $this->unmanagedRepository->save($existProduct);
        }
    }

    private function getProductVariantQty(\M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSkuCollection $skuCollection): ?int
    {
        if ($skuCollection->count() < 1) {
            return null;
        }

        $result = 0;
        $newProductVariants = $skuCollection->getAll();

        foreach ($newProductVariants as $variant) {
            $result += $variant->getQty();
        }

        return $result;
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductCollection $channelProductCollection
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct[]
     */
    private function processNew(ChannelProductCollection $channelProductCollection): array
    {
        $result = [];
        foreach ($channelProductCollection->getAll() as $item) {
            if ($item->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_BLOCKED) {
                continue;
            }

            if ($item->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                continue;
            }

            $unmanaged = $this->unmanagedCreateService->create($item);

            $result[] = $unmanaged;
        }

        return $result;
    }

    /**
     * @param \M2E\TikTokShop\Model\UnmanagedProduct[] $unmanagedListings
     */
    private function autoMapping(array $unmanagedListings): void
    {
        $this->mappingService->autoMapUnmanagedProducts($unmanagedListings);
    }

    private function findWarehouse(string $ttsWarehouseId): ?\M2E\TikTokShop\Model\Warehouse
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->warehousesByTtsId)) {
            $this->warehousesByTtsId = [];
            foreach ($this->warehouseRepository->findByShop($this->shop->getId()) as $warehouse) {
                $this->warehousesByTtsId[$warehouse->getWarehouseId()] = $warehouse;
            }
        }

        return $this->warehousesByTtsId[$ttsWarehouseId] ?? null;
    }
}
