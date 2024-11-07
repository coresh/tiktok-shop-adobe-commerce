<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

use M2E\TikTokShop\Model\ResourceModel\Listing\Other as ListingOtherResource;

class Other extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;

    private ?\M2E\TikTokShop\Model\Magento\Product\Cache $magentoProductModel = null;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private LogService $listingLogService;
    /** @var \M2E\TikTokShop\Model\Product\Repository */
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;
    private \M2E\TikTokShop\Model\Magento\Product\CacheFactory $productCacheFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Magento\Product\CacheFactory $productCacheFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data,
        );
        $this->accountRepository = $accountRepository;
        $this->shopRepository = $shopRepository;
        $this->listingLogService = $listingLogService;
        $this->listingProductRepository = $listingProductRepository;
        $this->productCacheFactory = $productCacheFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ListingOtherResource::class);
    }

    public function init(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        string $productId,
        string $skuId,
        int $status,
        string $title,
        string $sku,
        string $currency,
        float $price,
        int $qty,
        int $warehouseId,
        array $inventoryData,
        ?string $categoryId,
        array $categoriesData
    ): self {
        $this
            ->setData(ListingOtherResource::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(ListingOtherResource::COLUMN_SHOP_ID, $shop->getId())
            ->setData(ListingOtherResource::COLUMN_TTS_PRODUCT_ID, $productId)
            ->setData(ListingOtherResource::COLUMN_SKU_ID, $skuId)
            ->setData(ListingOtherResource::COLUMN_STATUS, $status)
            ->setData(ListingOtherResource::COLUMN_TITLE, $title)
            ->setData(ListingOtherResource::COLUMN_SKU, $sku)
            ->setData(ListingOtherResource::COLUMN_CURRENCY, $currency)
            ->setData(ListingOtherResource::COLUMN_PRICE, $price)
            ->setData(ListingOtherResource::COLUMN_QTY, $qty)
            ->setData(ListingOtherResource::COLUMN_WAREHOUSE_ID, $warehouseId)
            ->setData(ListingOtherResource::COLUMN_INVENTORY_DATA, json_encode($inventoryData, JSON_THROW_ON_ERROR))
            ->setData(ListingOtherResource::COLUMN_CATEGORY_ID, $categoryId)
            ->setData(ListingOtherResource::COLUMN_CATEGORIES_DATA, json_encode($categoriesData, JSON_THROW_ON_ERROR));

        $this->loadAccount($account)
            ->loadShop($shop);

        return $this;
    }

    // ----------------------------------------

    public function loadAccount(\M2E\TikTokShop\Model\Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getAccount(): \M2E\TikTokShop\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    // ---------------------------------------

    public function loadShop(\M2E\TikTokShop\Model\Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getShop(): \M2E\TikTokShop\Model\Shop
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->shop)) {
            return $this->shop;
        }

        return $this->shop = $this->shopRepository->get($this->getShopId());
    }

    // ---------------------------------------

    /**
     * @return \M2E\TikTokShop\Model\Magento\Product\Cache
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function getMagentoProduct(): ?\M2E\TikTokShop\Model\Magento\Product\Cache
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        if (!$this->hasMagentoProductId()) {
            throw new \M2E\TikTokShop\Model\Exception('Product id is not set');
        }

        return $this->magentoProductModel = $this->productCacheFactory->create()
                                                                      ->setStoreId($this->getRelatedStoreId())
                                                                      ->setProductId($this->getMagentoProductId());
    }

    // ----------------------------------------

    public function getAccountId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_ACCOUNT_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_SHOP_ID);
    }

    public function hasMagentoProductId(): bool
    {
        return !empty($this->getMagentoProductId());
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function getProductId(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_TTS_PRODUCT_ID);
    }

    public function getSkuId(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_SKU_ID);
    }

    public function setTitle(string $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_TITLE, $value);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_TITLE);
    }

    public function getSku(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_SKU);
    }

    public function setPrice(float $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_PRICE, $value);
    }

    public function getPrice(): float
    {
        return (float)$this->getData(ListingOtherResource::COLUMN_PRICE);
    }

    public function setQty(int $value): void
    {
        $this->setData(ListingOtherResource::COLUMN_QTY, $value);
    }

    public function getQty(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_QTY);
    }

    public function getWarehouseId(): ?int
    {
        $warehouseId = $this->getData(ListingOtherResource::COLUMN_WAREHOUSE_ID);
        if ($warehouseId === null) {
            return null;
        }

        return (int)$warehouseId;
    }

    public function getCurrency(): string
    {
        return (string)$this->getData(ListingOtherResource::COLUMN_CURRENCY);
    }

    public function getStatus(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_STATUS);
    }

    public function getInventoryData(): array
    {
        $json = $this->getData(ListingOtherResource::COLUMN_INVENTORY_DATA);
        if ($json === null) {
            return [];
        }

        return json_decode($json, true);
    }

    public function getCategoryId(): ?string
    {
        return $this->getData(ListingOtherResource::COLUMN_CATEGORY_ID);
    }

    public function getCategoriesData(): array
    {
        $json = $this->getData(ListingOtherResource::COLUMN_CATEGORIES_DATA);
        if ($json === null) {
            return [];
        }

        return json_decode($json, true);
    }

    // ---------------------------------------

    public function mapToMagentoProduct(int $magentoProductId): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function unmapProduct(): void
    {
        $this->setData(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID, null);
    }

    // ---------------------------------------

    public function moveToListingSucceed(): void
    {
        $listingProductId = $this->getMovedToListingProductId();
        $listingProduct = $this->listingProductRepository->get($listingProductId);
        if ($listingProduct->getId()) {
            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\TikTokShop\Helper\Data::INITIATOR_USER,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_MOVE_FROM_OTHER_LISTING,
                $this->listingLogService->getNextActionId(),
                (string)__('Item was Moved.'),
                \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
            );
        }

        $this->delete();
    }

    public function setMovedToListingProductId(int $id): void
    {
        $this->setData(ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID, $id);
    }

    private function getMovedToListingProductId(): int
    {
        return (int)$this->getData(ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID);
    }

    public function getRelatedStoreId(): int
    {
        return $this->getAccount()->getUnmanagedListingSettings()->getRelatedStoreForShopId($this->getShopId());
    }

    public function isListingCorrectForMove(\M2E\TikTokShop\Model\Listing $listing): bool
    {
        return $listing->getAccount()->getId() === $this->getAccountId()
            && $listing->getShop()->getId() === $this->getShopId();
    }

    public function setIdentifier(\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier)
    {
        $this->setData(ListingOtherResource::COLUMN_IDENTIFIER_ID, $identifier->getId());
        $this->setData(ListingOtherResource::COLUMN_IDENTIFIER_TYPE, $identifier->getType());
    }

    public function getIdentifier(): ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier
    {
        $id = $this->getData(ListingOtherResource::COLUMN_IDENTIFIER_ID);
        $code = $this->getData(ListingOtherResource::COLUMN_IDENTIFIER_TYPE);

        if (empty($id) || empty($code)) {
            return null;
        }

        return new \M2E\TikTokShop\Model\Product\VariantSku\Identifier($id, $code);
    }
}
