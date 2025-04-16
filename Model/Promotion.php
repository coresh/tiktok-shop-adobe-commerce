<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Promotion as TikTokShopPromotionResource;

class Promotion extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const TYPE_FIXED_PRICE = 'FIXED_PRICE';
    public const TYPE_DIRECT_DISCOUNT = 'DIRECT_DISCOUNT';
    public const TYPE_FLASH_SALE = 'FLASHSALE';

    public const STATUS_NOT_START = 'NOT_START';
    public const STATUS_ONGOING = 'ONGOING';

    public const PRODUCT_LEVEL_BY_PRODUCT = 'PRODUCT';
    public const PRODUCT_LEVEL_BY_VARIATION = 'VARIATION';
    private \M2E\TikTokShop\Model\Promotion\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\Product\Repository $productRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(TikTokShopPromotionResource::class);
    }

    public function init(
        int $accountId,
        int $shopId,
        string $promotionId,
        string $title,
        string $type,
        string $status,
        string $productLevel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): self {
        $this
            ->setData(TikTokShopPromotionResource::COLUMN_ACCOUNT_ID, $accountId)
            ->setData(TikTokShopPromotionResource::COLUMN_SHOP_ID, $shopId)
            ->setData(TikTokShopPromotionResource::COLUMN_PROMOTION_ID, $promotionId)
            ->setTitle($title)
            ->setType($type)
            ->setStatus($status)
            ->setProductLevel($productLevel)
            ->setStartDate($startDate)
            ->setEndDate($endDate);

        return $this;
    }

    public function updateFromChannel(\M2E\TikTokShop\Model\Promotion\Channel\Promotion $channel): bool
    {
        $updated = false;
        if ($this->getTitle() !== $channel->getTitle()) {
            $this->setTitle($channel->getTitle());

            $updated = true;
        }

        if ($this->getStatus() !== $channel->getStatus()) {
            $this->setStatus($channel->getStatus());

            $updated = true;
        }

        if ($this->getStartDate() != $channel->getStartDate()) {
            $this->setStartDate(clone $channel->getStartDate());

            $updated = true;
        }

        if ($this->getEndDate() != $channel->getEndDate()) {
            $this->setEndDate(clone $channel->getEndDate());

            $updated = true;
        }

        return $updated;
    }
    // ----------------------------------------

    public function getId(): int
    {
        return (int)$this->getDataByKey(TikTokShopPromotionResource::COLUMN_ID);
    }

    public function getAccountId(): int
    {
        return (int)$this->getDataByKey(TikTokShopPromotionResource::COLUMN_ACCOUNT_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getDataByKey(TikTokShopPromotionResource::COLUMN_SHOP_ID);
    }

    public function getPromotionId(): string
    {
        return $this->getDataByKey(TikTokShopPromotionResource::COLUMN_PROMOTION_ID);
    }

    // ----------------------------------------

    public function getTitle(): string
    {
        return $this->getDataByKey(TikTokShopPromotionResource::COLUMN_TITLE);
    }

    public function setTitle(string $title): self
    {
        $this->setData(TikTokShopPromotionResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function setType(string $type): self
    {
        $this->setData(TikTokShopPromotionResource::COLUMN_TYPE, $type);

        return $this;
    }

    public function getType(): string
    {
        return $this->getDataByKey(TikTokShopPromotionResource::COLUMN_TYPE);
    }

    public function getStatus(): string
    {
        return $this->getDataByKey(TikTokShopPromotionResource::COLUMN_STATUS);
    }

    public function setStatus(string $status): self
    {
        $this->setData(TikTokShopPromotionResource::COLUMN_STATUS, $status);

        return $this;
    }

    public function isStatusActive(): bool
    {
        return $this->getStatus() === self::STATUS_ONGOING;
    }

    public function isStatusNotStart(): bool
    {
        return $this->getStatus() === self::STATUS_NOT_START;
    }

    public function isActiveNow(): bool
    {
        $now = \M2E\Core\Helper\Date::createCurrentGmt();

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        return $now >= $startDate && $now <= $endDate;
    }

    public function setProductLevel(string $productLevel): self
    {
        $this->setData(TikTokShopPromotionResource::COLUMN_PRODUCT_LEVEL, $productLevel);

        return $this;
    }

    public function getProductLevel(): string
    {
        return $this->getData(TikTokShopPromotionResource::COLUMN_PRODUCT_LEVEL);
    }

    public function isProductLevelByProduct(): bool
    {
        return $this->getProductLevel() === self::PRODUCT_LEVEL_BY_PRODUCT;
    }

    public function isProductLevelByVariation(): bool
    {
        return $this->getProductLevel() === self::PRODUCT_LEVEL_BY_VARIATION;
    }

    // ----------------------------------------

    public function getStartDate(): \DateTime
    {
        $startDate = $this->getDataByKey(TikTokShopPromotionResource::COLUMN_START_DATE);

        return \M2E\Core\Helper\Date::createDateGmt($startDate);
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->setData(TikTokShopPromotionResource::COLUMN_START_DATE, $startDate->format('Y-m-d H:i:s'));

        return $this;
    }

    // ----------------------------------------

    public function getEndDate(): \DateTime
    {
        $endDate = $this->getDataByKey(TikTokShopPromotionResource::COLUMN_END_DATE);

        return \M2E\Core\Helper\Date::createDateGmt($endDate);
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->setData(TikTokShopPromotionResource::COLUMN_END_DATE, $endDate->format('Y-m-d H:i:s'));

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function getProducts(): array
    {
        return $this->productRepository->findProductsByPromotion($this->getId());
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function getSkus(): array
    {
        return $this->productRepository->findWithLevelVariantByPromotionId($this->getId());
    }
}
