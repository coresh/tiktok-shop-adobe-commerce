<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;

class UnmanagedProduct extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;

    /** @var \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku[] */
    private array $variants;
    private ?\M2E\TikTokShop\Model\Magento\Product\Cache $magentoProductModel = null;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Magento\Product\CacheFactory $productCacheFactory;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
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
        $this->productCacheFactory = $productCacheFactory;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(UnmanagedProductResource::class);
    }

    public function init(
        int $accountId,
        int $shopId,
        string $ttsProductId,
        int $status,
        string $title,
        string $categoryId,
        array $categoriesData
    ): self {
        $this
            ->setData(UnmanagedProductResource::COLUMN_ACCOUNT_ID, $accountId)
            ->setData(UnmanagedProductResource::COLUMN_SHOP_ID, $shopId)
            ->setData(UnmanagedProductResource::COLUMN_STATUS, $status)
            ->setData(UnmanagedProductResource::COLUMN_TTS_PRODUCT_ID, $ttsProductId)
            ->setData(UnmanagedProductResource::COLUMN_TITLE, $title)
            ->setData(UnmanagedProductResource::COLUMN_CATEGORY_ID, $categoryId)
            ->setData(UnmanagedProductResource::COLUMN_CATEGORIES_DATA, json_encode($categoriesData, JSON_THROW_ON_ERROR));

        return $this;
    }

    // ----------------------------------------

    public function getAccount(): \M2E\TikTokShop\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    // ---------------------------------------

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

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_ACCOUNT_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_SHOP_ID);
    }

    public function hasMagentoProductId(): bool
    {
        return !empty($this->getMagentoProductId());
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function getProductId(): string
    {
        return (string)$this->getData(UnmanagedProductResource::COLUMN_TTS_PRODUCT_ID);
    }

    public function setTitle(string $value): void
    {
        $this->setData(UnmanagedProductResource::COLUMN_TITLE, $value);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(UnmanagedProductResource::COLUMN_TITLE);
    }

    public function getSku(): ?string
    {
        $this->getVariants();
        if ($this->isSimple()) {
            return reset($this->variants)->getSku();
        }

        return null;
    }

    public function getSkuId(): ?string
    {
        $this->getVariants();
        if ($this->isSimple()) {
            return reset($this->variants)->getSkuId();
        }

        return null;
    }

    public function getPrice(): ?float
    {
        $this->getVariants();
        if ($this->isSimple()) {
            return reset($this->variants)->getCurrentPrice();
        }

        return null;
    }

    public function getCurrency(): ?string
    {
        $this->getVariants();
        if ($this->isSimple()) {
            return reset($this->variants)->getCurrency();
        }

        return null;
    }

    public function getQty(): ?int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_QTY);
    }

    public function setQty(int $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_QTY, $value);

        return $this;
    }

    // ----------------------------------------

    public function getStatus(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_STATUS);
    }

    public function setStatus(int $status): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_STATUS, $status);

        return $this;
    }

    public function isStatusNotListed(): bool
    {
        return $this->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED;
    }

    public function isStatusListed(): bool
    {
        return $this->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_LISTED;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === \M2E\TikTokShop\Model\Product::STATUS_INACTIVE;
    }

    // ----------------------------------------

    public function getCategoryId(): ?string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_CATEGORY_ID);
    }

    public function isSimple(): bool
    {
        return (bool)$this->getData(UnmanagedProductResource::COLUMN_IS_SIMPLE);
    }

    public function setIsSimple(bool $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_IS_SIMPLE, (int)$value);

        return $this;
    }

    public function getCategoriesData(): array
    {
        $json = $this->getData(UnmanagedProductResource::COLUMN_CATEGORIES_DATA);
        if ($json === null) {
            return [];
        }

        return json_decode($json, true);
    }

    // ---------------------------------------

    public function mapToMagentoProduct(int $magentoProductId): void
    {
        $this->setData(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function unmapProduct(): void
    {
        $this->setData(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, null);
    }

    // ---------------------------------------

    public function getRelatedStoreId(): int
    {
        return $this->getAccount()->getUnmanagedListingSettings()->getRelatedStoreForShopId($this->getShopId());
    }

    public function isListingCorrectForMove(\M2E\TikTokShop\Model\Listing $listing): bool
    {
        return $listing->getAccount()->getId() === $this->getAccountId()
            && $listing->getShop()->getId() === $this->getShopId();
    }

    public function getFirstVariant(): \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku
    {
        $variants = $this->getVariants();

        return reset($variants);
    }

    /**
     * @return \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku[]
     */
    public function getVariants(): array
    {
        $this->loadVariants();

        return array_values($this->variants);
    }

    public function getSalesAttributeNames(): array
    {
        $names = [];
        $variants = $this->getVariants();

        foreach ($variants as $variant) {
            $salesAttributes = $variant->getSalesAttributes();
            foreach ($salesAttributes as $attribute) {
                if (!in_array($attribute->getName(), $names)) {
                    $names[] = $attribute->getName();
                }
            }
        }

        return $names;
    }

    private function loadVariants(): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->variants)) {
            return;
        }

        $variants = [];
        foreach ($this->unmanagedRepository->findVariantsByProduct($this) as $variant) {
            $variants[$variant->getSkuId()] = $variant;
        }

        $this->variants = $variants;
    }

    public function calculateDataByVariants(): self
    {
        $this->setQty($this->getVariantsQtySum());
        $this->setIsSimple(true);

        if (count($this->getVariants()) > 1) {
            $this->setIsSimple(false);

            $this
                ->setMinPrice($this->getMinPriceFromVariants())
                ->setMaxPrice($this->getMaxPriceFromVariants());
        }

        return $this;
    }

    public function setMinPrice(?float $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_MIN_PRICE, $value);

        return $this;
    }

    public function getMinPrice(): ?float
    {
        $minPrice = $this->getData(UnmanagedProductResource::COLUMN_MIN_PRICE);
        if ($minPrice === null) {
            return null;
        }

        return (float)$minPrice;
    }

    public function setMaxPrice(?float $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_MAX_PRICE, $value);

        return $this;
    }

    public function getMaxPrice(): ?float
    {
        $maxPrice = $this->getData(UnmanagedProductResource::COLUMN_MAX_PRICE);
        if ($maxPrice === null) {
            return null;
        }

        return (float)$maxPrice;
    }

    private function getVariantsQtySum(): int
    {
        $result = 0;
        foreach ($this->getVariants() as $variant) {
            $result += $variant->getQty();
        }

        return $result;
    }

    private function getMinPriceFromVariants(): ?float
    {
        $prices = $this->getPricesFromVariantSku();

        if (empty($prices)) {
            return null;
        }

        if (count($prices) === 1) {
            return (float)reset($prices);
        }

        return (float)min($this->getPricesFromVariantSku());
    }

    private function getMaxPriceFromVariants(): ?float
    {
        $prices = $this->getPricesFromVariantSku();

        if (empty($prices)) {
            return null;
        }

        if (count($prices) === 1) {
            return (float)reset($prices);
        }

        return (float)max($this->getPricesFromVariantSku());
    }

    private function getPricesFromVariantSku(): array
    {
        $prices = [];
        foreach ($this->getVariants() as $variant) {
            $prices[] = $variant->getCurrentPrice();
        }

        return $prices;
    }
}
