<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

class Updater
{
    private Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\Listing\Other\MappingService $mappingService;
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;
    private \M2E\TikTokShop\Model\Listing\OtherFactory $otherFactory;
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;
    /** @var array<string, \M2E\TikTokShop\Model\Warehouse> */
    private array $warehousesByTtsId;

    public function __construct(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Listing\OtherFactory $otherFactory,
        \M2E\TikTokShop\Model\Listing\Other\Repository $unmanagedRepository,
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository,
        \M2E\TikTokShop\Model\Listing\Other\MappingService $mappingService
    ) {
        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->listingProductRepository = $listingProductRepository;
        $this->otherFactory = $otherFactory;
        $this->warehouseRepository = $warehouseRepository;
        $this->account = $account;
        $this->shop = $shop;
    }

    public function process(TtsProductCollection $ttsProductCollection): ?TtsProductCollection
    {
        if ($ttsProductCollection->empty()) {
            return null;
        }

        $this->removeWithUnknownWarehouse($ttsProductCollection);
        $existInListingCollection = $this->removeExistInListingProduct($ttsProductCollection);

        $this->processExist($ttsProductCollection);
        $unmanagedItems = $this->processNew($ttsProductCollection);

        // remove not exist

        $this->autoMapping($unmanagedItems);

        return $existInListingCollection;
    }

    private function removeWithUnknownWarehouse(TtsProductCollection $ttsProductsCollection): void
    {
        foreach ($ttsProductsCollection->getAll() as $channelProduct) {
            foreach ($channelProduct->getVariantSkuCollection()->getAll() as $sku) {
                if ($this->findWarehouse($sku->getWarehouseId()) === null) {
                    $ttsProductsCollection->remove($channelProduct->getProductId());

                    break;
                }
            }
        }
    }

    private function removeExistInListingProduct(TtsProductCollection $collection): TtsProductCollection
    {
        $existInListingCollection = new \M2E\TikTokShop\Model\Listing\Other\TtsProductCollection();
        if ($collection->empty()) {
            return $existInListingCollection;
        }

        $existed = $this->listingProductRepository->findByTtsProductIds(
            $collection->getProductsIds(),
            $this->account->getId(),
            $this->shop->getId()
        );

        foreach ($existed as $product) {
            $existInListingCollection->add($collection->get($product->getTTSProductId()));

            $collection->remove($product->getTTSProductId());
        }

        return $existInListingCollection;
    }

    private function processExist(TtsProductCollection $collection): void
    {
        if ($collection->empty()) {
            return;
        }

        $existProducts = $this->unmanagedRepository->findByProductIds(
            $collection->getProductsIds(),
            $this->account->getId(),
            $this->shop->getId(),
        );

        foreach ($existProducts as $existProduct) {
            if (!$collection->has($existProduct->getProductId())) {
                continue;
            }

            $new = $collection->get($existProduct->getProductId());

            $collection->remove($existProduct->getProductId());

            // removed
            if ($new->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_BLOCKED) {
                $this->unmanagedRepository->remove($existProduct);

                continue;
            }

            if ($new->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                $this->unmanagedRepository->remove($existProduct);

                continue;
            }

            if ($existProduct->getTitle() !== $new->getTitle()) {
                $existProduct->setTitle($new->getTitle());
            }

            $firstChannelVariation = $new->getVariantSkuCollection()->getFirst();
            if ($existProduct->getQty() !== $firstChannelVariation->getQty()) {
                $existProduct->setQty($firstChannelVariation->getQty());
            }

            if ($existProduct->getPrice() !== $firstChannelVariation->getPrice()) {
                $existProduct->setPrice($firstChannelVariation->getPrice());
            }

            $this->unmanagedRepository->save($existProduct);
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing\Other\TtsProductCollection $collection
     *
     * @return \M2E\TikTokShop\Model\Listing\Other[]
     */
    private function processNew(TtsProductCollection $collection): array
    {
        $result = [];
        foreach ($collection->getAll() as $item) {
            if ($item->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_BLOCKED) {
                continue;
            }

            if ($item->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                continue;
            }

            if ($item->getVariantSkuCollection()->count() > 1) {
                continue;
            }

            $firstChannelVariation = $item->getVariantSkuCollection()->getFirst();

            $other = $this->otherFactory->create();
            $other->init(
                $this->account,
                $this->shop,
                $item->getProductId(),
                $firstChannelVariation->getSkuId(),
                $item->getStatus(),
                $item->getTitle(),
                $firstChannelVariation->getSku(),
                $firstChannelVariation->getCurrency(),
                $firstChannelVariation->getPrice(),
                $firstChannelVariation->getQty(),
                $this->getWarehouse($firstChannelVariation->getWarehouseId())->getId(),
                $firstChannelVariation->getInventoryData(),
                $item->getCategoryId(),
                $item->getCategoriesData()
            );

            if ($firstChannelVariation->getIdentifier() !== null) {
                $other->setIdentifier($firstChannelVariation->getIdentifier());
            }

            $this->unmanagedRepository->create($other);

            $result[] = $other;
        }

        return $result;
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing\Other[] $otherListings
     */
    private function autoMapping(array $otherListings): void
    {
        $this->mappingService->autoMapOtherListingsProducts($otherListings);
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

    private function getWarehouse(string $ttwWarehouseId): \M2E\TikTokShop\Model\Warehouse
    {
        $warehouse = $this->findWarehouse($ttwWarehouseId);
        if ($warehouse === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic("Warehouse $ttwWarehouseId not found.");
        }

        return $warehouse;
    }
}
