<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;

class Product extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const ACTION_LIST = 1;
    public const ACTION_RELIST = 2;
    public const ACTION_REVISE = 3;
    public const ACTION_STOP = 4;
    public const ACTION_DELETE = 5;

    public const STATUS_NOT_LISTED = 0;
    public const STATUS_LISTED = 2;
    public const STATUS_INACTIVE = 8;
    public const STATUS_BLOCKED = 6;

    public const STATUS_CHANGER_UNKNOWN = 0;
    public const STATUS_CHANGER_SYNCH = 1;
    public const STATUS_CHANGER_USER = 2;
    public const STATUS_CHANGER_COMPONENT = 3;
    public const STATUS_CHANGER_OBSERVER = 4;

    public const IS_GIFT_ON = 1;
    public const IS_GIFT_OFF = 0;

    public const MOVING_LISTING_OTHER_SOURCE_KEY = 'moved_from_listing_other_id';

    public const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED = 'channel_status_changed';
    public const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED = 'channel_qty_changed';
    public const INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED = 'channel_price_changed';
    public const INSTRUCTION_TYPE_CHANNEL_MANUFACTURER_CHANGED = 'channel_manufacturer_changed';
    public const INSTRUCTION_TYPE_CHANNEL_RESPONSIBLE_PERSON_CHANGED = 'channel_responsible_person_changed';
    public const INSTRUCTION_TYPE_VARIANT_SKU_REMOVED = 'variant_sku_removed';
    public const INSTRUCTION_TYPE_VARIANT_SKU_ADDED = 'variant_sku_added';

    private \M2E\TikTokShop\Model\Listing $listing;

    /** @var \M2E\TikTokShop\Model\Product\VariantSku[] */
    private array $variants;

    private \M2E\TikTokShop\Model\Magento\Product\Cache $magentoProductModel;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private ?Category\Dictionary $categoryDictionary = null;
    private ?\M2E\TikTokShop\Model\Template\SellingFormat $sellingFormatTemplateModel = null;
    private ?\M2E\TikTokShop\Model\Template\Synchronization $synchronizationTemplateModel = null;
    private ?\M2E\TikTokShop\Model\Template\Description $descriptionTemplateModel = null;
    private \M2E\TikTokShop\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Description\RendererFactory */
    private TikTokShop\Listing\Product\Description\RendererFactory $descriptionRendererFactory;
    /** @var \M2E\TikTokShop\Model\ProductPromotionService */
    private ProductPromotionService $productPromotionService;
    /** @var \M2E\TikTokShop\Model\GlobalProduct\Repository */
    private GlobalProduct\Repository $globalProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Description\RendererFactory $descriptionRendererFactory,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\ProductPromotionService $productPromotionService,
        \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->listingRepository = $listingRepository;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->productRepository = $productRepository;
        $this->descriptionRendererFactory = $descriptionRendererFactory;
        $this->productPromotionService = $productPromotionService;
        $this->globalProductRepository = $globalProductRepository;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(ListingProductResource::class);
    }

    // ----------------------------------------

    public function init(Listing $listing, int $magentoProductId, bool $isSimple, int $categoryDictionaryId): self
    {
        $this
            ->setListingId($listing->getId())
            ->setMagentoProductId($magentoProductId)
            ->setStatusNotListed(self::STATUS_CHANGER_USER)
            ->setData(ListingProductResource::COLUMN_IS_SIMPLE, (int)$isSimple)
            ->setTemplateCategoryId($categoryDictionaryId);

        $this->variants = [];
        $this->initListing($listing);

        return $this;
    }

    public function fillFromUnmanagedProduct(\M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct): self
    {
        $this->setTtsProductId($unmanagedProduct->getProductId())
             ->setStatus($unmanagedProduct->getStatus(), self::STATUS_CHANGER_COMPONENT)
             ->setOnlineTitle($unmanagedProduct->getTitle())
             ->setOnlineQty($unmanagedProduct->getQty())
             ->setIsGift($unmanagedProduct->isGift());

        if ($unmanagedProduct->getCategoryId() !== null) {
            $this->setOnlineMainCategory($unmanagedProduct->getCategoryId());
        }

        $additionalData = $this->getAdditionalData();
        $additionalData[self::MOVING_LISTING_OTHER_SOURCE_KEY] = $unmanagedProduct->getId();

        $this->setAdditionalData($additionalData);

        return $this;
    }

    // ----------------------------------------

    public function initListing(\M2E\TikTokShop\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\TikTokShop\Model\Listing
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listing)) {
            return $this->listing;
        }

        return $this->listing = $this->listingRepository->get($this->getListingId());
    }

    public function getAccount(): Account
    {
        return $this->getListing()->getAccount();
    }

    public function getShop(): \M2E\TikTokShop\Model\Shop
    {
        return $this->getListing()->getShop();
    }

    // ---------------------------------------

    public function addVariant(\M2E\TikTokShop\Model\Product\VariantSku $variant): self
    {
        $this->loadVariants();

        if (count($this->variants) > 0 && $this->isSimple()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                'Unable to init variant product',
                ['variant_sku' => $variant->getSku()],
            );
        }

        $this->variants[$variant->getMagentoProductId()] = $variant;

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\Product\VariantSku[]
     */
    public function getVariants(): array
    {
        $this->loadVariants();

        return array_values($this->variants);
    }

    public function findVariantBySkuId(string $skuId): ?\M2E\TikTokShop\Model\Product\VariantSku
    {
        foreach ($this->getVariants() as $variant) {
            if ($variant->getSkuId() === $skuId) {
                return $variant;
            }
        }

        return null;
    }

    private function loadVariants(): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->variants)) {
            return;
        }

        $variants = [];
        foreach ($this->productRepository->findVariantsByProduct($this) as $variant) {
            $variants[$variant->getMagentoProductId()] = $variant;
        }

        $this->variants = $variants;
    }

    public function getFirstVariant(): \M2E\TikTokShop\Model\Product\VariantSku
    {
        $variants = $this->getVariants();

        return reset($variants);
    }

    /**
     * @return \M2E\TikTokShop\Model\Product\VariantSku\OnlineData[]
     */
    public function getVariantOnlineData(): array
    {
        $result = [];
        foreach ($this->getVariants() as $variant) {
            $result[] = $variant->getOnlineData();
        }

        return $result;
    }

    // ---------------------------------------

    public function getMagentoProduct(): \M2E\TikTokShop\Model\Magento\Product\Cache
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->magentoProductModel)) {
            $this->magentoProductModel = $this->magentoProductFactory->create();
            $this->magentoProductModel->setProductId($this->getMagentoProductId());
            $this->magentoProductModel->setStoreId($this->getListing()->getStoreId());
            $this->magentoProductModel->setStatisticId($this->getId());
        }

        return $this->magentoProductModel;
    }

    // ----------------------------------------

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getListingId(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_LISTING_ID);
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function isSimple(): bool
    {
        return (bool)$this->getData(ListingProductResource::COLUMN_IS_SIMPLE);
    }

    public function getOnlineQty(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_ONLINE_QTY);
    }

    // ---------------------------------------

    public function isStatusNotListed(): bool
    {
        return $this->getStatus() === self::STATUS_NOT_LISTED;
    }

    public function isStatusBlocked(): bool
    {
        return $this->getStatus() === self::STATUS_BLOCKED;
    }

    public function isStatusListed(): bool
    {
        return $this->getStatus() === self::STATUS_LISTED;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === self::STATUS_INACTIVE;
    }

    public function setStatusListed(string $ttsProductId, int $changer): self
    {
        $this
            ->setStatus(self::STATUS_LISTED, $changer)
            ->setTtsProductId($ttsProductId);

        return $this;
    }

    public function setStatusNotListed(int $changer): self
    {
        $this->setStatus(self::STATUS_NOT_LISTED, $changer)
            ->setData(ListingProductResource::COLUMN_TTS_PRODUCT_ID, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_TITLE, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_DESCRIPTION, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_BRAND_NAME, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_QTY, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_CATEGORY, null)
        ;

        return $this;
    }

    public function setStatusInactive(int $changer): self
    {
        $this->setStatus(self::STATUS_INACTIVE, $changer);

        return $this;
    }

    public function setStatusBlocked(int $changer): self
    {
        $this->setStatus(self::STATUS_BLOCKED, $changer);

        return $this;
    }

    public function setStatus(int $status, int $changer): self
    {
        $this->setData(ListingProductResource::COLUMN_STATUS, $status)
            ->setStatusChanger($changer);

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_STATUS);
    }

    // ----------------------------------------

    public function isStatusChangerUser(): bool
    {
        return $this->getStatusChanger() === self::STATUS_CHANGER_USER;
    }

    public function getStatusChanger(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_STATUS_CHANGER);
    }

    // ----------------------------------------

    public function setGlobalProductId(int $globalProductId): self
    {
        $this->setData(ListingProductResource::COLUMN_GLOBAL_PRODUCT_ID, $globalProductId);

        return $this;
    }

    public function getGlobalProductId(): ?int
    {
        $globalProductId = $this->getData(ListingProductResource::COLUMN_GLOBAL_PRODUCT_ID);
        if (empty($globalProductId)) {
            return null;
        }

        return (int)$globalProductId;
    }

    public function isGlobalProduct(): bool
    {
        return $this->getGlobalProductId() !== null;
    }

    public function getGlobalProduct(): \M2E\TikTokShop\Model\GlobalProduct
    {
        return $this->globalProductRepository->get($this->getGlobalProductId());
    }

    // ----------------------------------------

    public function setAdditionalData(array $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ADDITIONAL_DATA, json_encode($value));

        return $this;
    }

    public function getAdditionalData(): array
    {
        $value = $this->getData(ListingProductResource::COLUMN_ADDITIONAL_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    // ---------------------------------------

    public function isListable(): bool
    {
        if (
            !$this->hasCategoryTemplate()
            && !$this->isGlobalProduct()
        ) {
            return false;
        }

        if ($this->isStatusBlocked()) {
            return false;
        }

        return $this->isStatusNotListed()
            || $this->isStatusInactive();
    }

    public function isRelistable(): bool
    {
        return $this->isStatusInactive()
            && !$this->isStatusBlocked();
    }

    public function isRevisable(): bool
    {
        return $this->isStatusListed() && !$this->isStatusBlocked();
    }

    public function isStoppable(): bool
    {
        return $this->isStatusListed()
            && !$this->isStatusBlocked();
    }

    public function isRetirable(): bool
    {
        return (
                $this->isStatusListed()
                || $this->isStatusInactive()
            ) && !$this->isStatusBlocked();
    }

    // ----------------------------------------

    public function getTTSProductId(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_TTS_PRODUCT_ID);
    }

    public function getCategoryDictionary(): Category\Dictionary
    {
        if (isset($this->categoryDictionary)) {
            return $this->categoryDictionary;
        }

        if (!$this->hasCategoryTemplate() && !$this->isGlobalProduct()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Category was not selected.');
        }

        return $this->categoryDictionary = $this->categoryDictionaryRepository->get($this->getTemplateCategoryId());
    }

    // ---------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getSellingFormatTemplate(): \M2E\TikTokShop\Model\Template\SellingFormat
    {
        return $this->getListing()->getTemplateSellingFormat();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getSynchronizationTemplate(): \M2E\TikTokShop\Model\Template\Synchronization
    {
        return $this->getListing()->getTemplateSynchronization();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getDescriptionTemplate(): \M2E\TikTokShop\Model\Template\Description
    {
        return $this->getListing()->getTemplateDescription();
    }

    public function getRenderedDescription(): string
    {
        return $this->descriptionRendererFactory
            ->create($this)
            ->parseTemplate($this->getDescriptionTemplateSource()->getDescription());
    }

    // ---------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getSellingFormatTemplateSource(): \M2E\TikTokShop\Model\Template\SellingFormat\Source
    {
        return $this->getSellingFormatTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getDescriptionTemplateSource(): \M2E\TikTokShop\Model\Template\Description\Source
    {
        return $this->getDescriptionTemplate()->getSource($this->getMagentoProduct());
    }

    public function hasCategoryTemplate(): bool
    {
        return !empty($this->getData(ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID));
    }

    public function setTemplateCategoryId(int $id): self
    {
        // or set value from \M2E\TikTokShop\Model\Product\Repository::setCategoryTemplate
        $this->setData(ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID, $id);

        return $this;
    }

    public function removeTemplateCategoryId(): self
    {
        $this->setData(ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID, null);

        return $this;
    }

    public function getTemplateCategoryId(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID);
    }

    // ---------------------------------------

    public function getOnlineTitle(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_TITLE);
    }

    public function setOnlineDescription(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_DESCRIPTION, $value);

        return $this;
    }

    public function getOnlineDescription(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_DESCRIPTION);
    }

    // ---------------------------------------

    public function getOnlineMainCategory(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_CATEGORY);
    }

    public function setOnlineBrandName(?string $name): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_BRAND_NAME, $name);

        return $this;
    }

    public function getOnlineBrandName(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_BRAND_NAME);
    }

    public function setOnlineBrandId(?string $id): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_BRAND_ID, $id);

        return $this;
    }

    public function getOnlineBrandId(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_BRAND_ID);
    }

    public function recalculateOnlineDataByVariants(): self
    {
        $this
            ->setOnlineQty($this->getVariantSkuOnlineQtySum())
            ->setMinPrice($this->getMinOnlinePriceFromVariantSku())
            ->setMaxPrice($this->getMaxOnlinePriceFromVariantSku());

        return $this;
    }

    public function setOnlineQty(int $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_QTY, $value);

        return $this;
    }

    public function setMinPrice(?float $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_MIN_PRICE, $value);

        return $this;
    }

    public function setMaxPrice(?float $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_MAX_PRICE, $value);

        return $this;
    }

    public function getMinPrice(): ?float
    {
        $minPrice = $this->getData(ListingProductResource::COLUMN_ONLINE_MIN_PRICE);
        if ($minPrice === null) {
            return null;
        }

        return (float)$minPrice;
    }

    public function getMaxPrice(): ?float
    {
        $maxPrice = $this->getData(ListingProductResource::COLUMN_ONLINE_MAX_PRICE);
        if ($maxPrice === null) {
            return null;
        }

        return (float)$maxPrice;
    }

    // ---------------------------------------

    public function changeListing(\M2E\TikTokShop\Model\Listing $listing): self
    {
        $this->setListingId($listing->getId());
        $this->initListing($listing);

        return $this;
    }

    private function setListingId(int $listingId): self
    {
        $this->setData(ListingProductResource::COLUMN_LISTING_ID, $listingId);

        return $this;
    }

    private function setMagentoProductId(int $magentoProductId): self
    {
        $this->setData(ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    private function setTtsProductId(string $productId): self
    {
        $this->setData(ListingProductResource::COLUMN_TTS_PRODUCT_ID, $productId);

        return $this;
    }

    public function setOnlineTitle(string $onlineTitle): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_TITLE, $onlineTitle);

        return $this;
    }

    public function setOnlineMainCategory(string $mainCategory): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_CATEGORY, $mainCategory);

        return $this;
    }

    public function setOnlineCategoryData(string $data): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA, $data);

        return $this;
    }

    public function getOnlineCategoryData(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA);
    }

    public function setOnlineManufacturerId(?string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_MANUFACTURER_ID, $value);

        return $this;
    }

    public function getOnlineManufacturerId(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_ONLINE_MANUFACTURER_ID);
    }

    public function setOnlineResponsiblePersonIds(array $values): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS, json_encode($values, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function getOnlineResponsiblePersonIds(): array
    {
        $value = $this->getData(ListingProductResource::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS);
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }

    // ----------------------------------------

    private function setStatusChanger(int $statusChanger): void
    {
        $this->validateStatusChanger($statusChanger);

        $this->setData(ListingProductResource::COLUMN_STATUS_CHANGER, $statusChanger);
    }

    // ----------------------------------------

    public static function createOnlineDescription(string $description): string
    {
        return \M2E\Core\Helper\Data::md5String($description);
    }

    // region qty

    private function getVariantSkuOnlineQtySum(): int
    {
        $result = 0;
        foreach ($this->getVariants() as $variant) {
            $result += $variant->getOnlineQty();
        }

        return $result;
    }

    // endregion

    private function getMinOnlinePriceFromVariantSku(): ?float
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

    private function getMaxOnlinePriceFromVariantSku(): ?float
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
            if ($variant->isStatusNotListed()) {
                continue;
            }

            $prices[] = $variant->getOnlineCurrentPrice();
        }

        return $prices;
    }

    // ----------------------------------------

    public static function getStatusTitle(int $status): string
    {
        $statuses = [
            self::STATUS_NOT_LISTED => (string)__('Not Listed'),
            self::STATUS_LISTED => (string)__('Active'),
            self::STATUS_BLOCKED => (string)__('Incomplete'),
            self::STATUS_INACTIVE => (string)__('Inactive'),
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    // ----------------------------------------

    private function validateStatusChanger(int $changer): void
    {
        $allowed = [
            self::STATUS_CHANGER_SYNCH,
            self::STATUS_CHANGER_USER,
            self::STATUS_CHANGER_COMPONENT,
            self::STATUS_CHANGER_OBSERVER,
        ];

        if (!in_array($changer, $allowed)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(sprintf('Status changer %s not valid.', $changer));
        }
    }

    // ----------------------------------------

    public static function getProductLinkOnChannel(string $ttsProductId, ?string $region): string
    {
        $url = sprintf(
            'https://shop.tiktok.com/view/product/%s',
            $ttsProductId
        );

        if (!empty($region)) {
            $url .= sprintf('?region=%s', $region);
        }

        return $url;
    }

    public function hasBlockingByError(): bool
    {
        $rawDate = $this->getData(ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE);
        if (empty($rawDate)) {
            return false;
        }

        $lastBlockingDate = \M2E\Core\Helper\Date::createDateGmt($rawDate);
        $twentyFourHoursAgoDate = \M2E\Core\Helper\Date::createCurrentGmt()->modify('-24 hour');

        return $lastBlockingDate->getTimestamp() > $twentyFourHoursAgoDate->getTimestamp();
    }

    public function removeBlockingByError(): self
    {
        $this->setData(ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE, null);

        return $this;
    }

    public function hasActiveOrNotStartPromotion(): bool
    {
        return $this->productPromotionService->isProductOnPromotion($this);
    }

    //region ListingQuality
    public function setListingQuality(Product\ListingQuality $listingQuality): self
    {
        if (!$listingQuality->hasTier()) {
            $this->setData(ListingProductResource::COLUMN_LISTING_QUALITY_TIER, null);
            $this->setData(ListingProductResource::COLUMN_LISTING_QUALITY_RECOMMENDATIONS, null);

            return $this;
        }

        if ($listingQuality->isTierGood()) {
            $this->setData(ListingProductResource::COLUMN_LISTING_QUALITY_TIER, $listingQuality->getTier());
            $this->setData(ListingProductResource::COLUMN_LISTING_QUALITY_RECOMMENDATIONS, null);

            return $this;
        }

        $this->setData(ListingProductResource::COLUMN_LISTING_QUALITY_TIER, $listingQuality->getTier());
        $recommendationsArray = $listingQuality->getRecommendationCollection()->toArray();

        $this->setData(
            ListingProductResource::COLUMN_LISTING_QUALITY_RECOMMENDATIONS,
            json_encode($recommendationsArray, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getListingQuality(): Product\ListingQuality
    {
        $listingQuality = new Product\ListingQuality($this->getListingQuantityTier());

        $recommendations = $this->getListingQuantityRecommendations();
        foreach ($recommendations as $recommendation) {
            $listingQuality->addRecommendation(new Product\ListingQuality\Recommendation(
                $recommendation['code'],
                $recommendation['field'],
                $recommendation['section'],
                $recommendation['how_to_solve'],
                $recommendation['quality_tier'],
            ));
        }

        return $listingQuality;
    }

    private function getListingQuantityTier(): ?string
    {
        $tier = (string)$this->getData(ListingProductResource::COLUMN_LISTING_QUALITY_TIER);
        if (empty($tier)) {
            return null;
        }

        return $tier;
    }

    /**
     * @return array{ array{code:string, field: string, section: string, how_to_solve: string, quality_tier: string} }
     */
    private function getListingQuantityRecommendations(): array
    {
        $recommendations = $this->getData(ListingProductResource::COLUMN_LISTING_QUALITY_RECOMMENDATIONS);
        if (empty($recommendations)) {
            return [];
        }

        return json_decode($recommendations, true);
    }
    //endregion

    public function isGift(): bool
    {
        return (bool)$this->getData(ListingProductResource::COLUMN_IS_GIFT);
    }

    public function setIsGift(bool $value): self
    {
        $this->setData(ListingProductResource::COLUMN_IS_GIFT, $value);

        return $this;
    }

    public function setManufacturerConfigId(int $manufacturerConfigId): self
    {
        $this->setData(ListingProductResource::COLUMN_MANUFACTURER_CONFIG_ID, $manufacturerConfigId);

        return $this;
    }

    public function getManufacturerConfigId(): ?int
    {
        $value = $this->getData(ListingProductResource::COLUMN_MANUFACTURER_CONFIG_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }
}
