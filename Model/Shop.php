<?php

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Shop as ShopResource;

class Shop extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const LOCK_NICK = 'shop';

    public const TYPE_CROSS_BORDER = 1;
    public const TYPE_LOCAL_TO_LOCAL = 2;

    public const REGION_US = 'US';
    public const REGION_GB = 'GB';
    public const REGION_ES = 'ES';

    public const REGION_EU = [
        self::REGION_ES,
    ];

    private Account\Repository $accountRepository;
    private Warehouse\Repository $warehouseRepository;

    private \M2E\TikTokShop\Model\Account $account;
    /** @var \M2E\TikTokShop\Model\Warehouse[] */
    private array $warehouses;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        Warehouse\Repository $warehouseRepository,
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
        $this->warehouseRepository = $warehouseRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ShopResource::class);
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getAccount(): \M2E\TikTokShop\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        $account = $this->accountRepository->find($this->getAccountId());

        if ($account === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Account must be created');
        }

        return $this->account = $account;
    }

    public function hasDefaultWarehouse(): bool
    {
        foreach ($this->getWarehouses() as $warehouse) {
            if ($warehouse->isDefault()) {
                return true;
            }
        }

        return false;
    }

    public function getDefaultWarehouse(): \M2E\TikTokShop\Model\Warehouse
    {
        foreach ($this->getWarehouses() as $warehouse) {
            if ($warehouse->isDefault()) {
                return $warehouse;
            }
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Default Warehouse not found.');
    }

    /**
     * @param \M2E\TikTokShop\Model\Warehouse[] $warehouses
     *
     * @return void
     */
    public function setWarehouses(array $warehouses): void
    {
        $this->warehouses = $warehouses;
    }

    /**
     * @return \M2E\TikTokShop\Model\Warehouse[]
     */
    public function getWarehouses(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->warehouses)) {
            return $this->warehouses;
        }

        $this->warehouses = $this->warehouseRepository->findByShop($this->getId());
        foreach ($this->warehouses as $warehouse) {
            $warehouse->setShop($this);
        }

        return $this->warehouses;
    }

    public function create(
        \M2E\TikTokShop\Model\Account $account,
        string $shopId,
        string $shopName,
        string $region,
        int $type
    ): self {
        $this->setData(ShopResource::COLUMN_ACCOUNT_ID, $account->getId())
             ->setData(ShopResource::COLUMN_SHOP_ID, $shopId)
             ->setShopName($shopName)
             ->setRegion($region)
             ->setType($type);

        $this->setAccount($account);

        return $this;
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ShopResource::COLUMN_ACCOUNT_ID);
    }

    public function getShopId(): string
    {
        return $this->getData(ShopResource::COLUMN_SHOP_ID);
    }

    public function setShopName(string $name): self
    {
        $this->setData(ShopResource::COLUMN_SHOP_NAME, $name);

        return $this;
    }

    public function getShopName(): string
    {
        return $this->getData(ShopResource::COLUMN_SHOP_NAME);
    }

    public function isRegionUS(): bool
    {
        return $this->getRegion() === self::REGION_US;
    }

    public function isRegionGB(): bool
    {
        return $this->getRegion() === self::REGION_GB;
    }

    public function isRegionES(): bool
    {
        return $this->getRegion() === self::REGION_ES;
    }

    public function isRegionEU(): bool
    {
        return in_array($this->getRegion(), self::REGION_EU);
    }

    public function setRegion(string $region): self
    {
        $this->setData(ShopResource::COLUMN_REGION, $region);

        return $this;
    }

    public function getRegion(): string
    {
        return $this->getData(ShopResource::COLUMN_REGION);
    }

    public function isTypeCrossBorder(): bool
    {
        return $this->getType() === self::TYPE_CROSS_BORDER;
    }

    public function isTypeLoclToLocal(): bool
    {
        return $this->getType() === self::TYPE_LOCAL_TO_LOCAL;
    }

    public function setType(int $type): self
    {
        if (!in_array($type, [self::TYPE_CROSS_BORDER, self::TYPE_LOCAL_TO_LOCAL])) {
            throw new \M2E\TikTokShop\Model\Exception\Logic("Shop type '$type' is not valid.");
        }

        $this->setData(ShopResource::COLUMN_TYPE, $type);

        return $this;
    }

    public function getType(): int
    {
        return (int)$this->getData(ShopResource::COLUMN_TYPE);
    }

    public function setOrdersLastSyncDate(\DateTime $date): self
    {
        $this->setData(ShopResource::COLUMN_ORDER_LAST_SYNC, $date);

        return $this;
    }

    public function getOrdersLastSyncDate(): ?\DateTime
    {
        $value = $this->getData(ShopResource::COLUMN_ORDER_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \M2E\TikTokShop\Helper\Date::createDateGmt($value);
    }

    public function setInventoryLastSyncDate(\DateTimeInterface $date): self
    {
        $this->setData(ShopResource::COLUMN_INVENTORY_LAST_SYNC, $date);

        return $this;
    }

    public function resetInventoryLastSyncDate(): self
    {
        $this->setData(ShopResource::COLUMN_INVENTORY_LAST_SYNC);

        return $this;
    }

    public function getInventoryLastSyncDate(): ?\DateTimeImmutable
    {
        $value = $this->getData(ShopResource::COLUMN_INVENTORY_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable(\M2E\TikTokShop\Helper\Date::createDateGmt($value));
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\TikTokShop\Helper\Date::createDateGmt(
            $this->getData(ShopResource::COLUMN_UPDATE_DATE),
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\TikTokShop\Helper\Date::createDateGmt(
            $this->getData(ShopResource::COLUMN_CREATE_DATE),
        );
    }

    // ----------------------------------------

    public function getCurrencyCode(): string
    {
        return self::getCurrencyCodeByRegion($this->getRegion());
    }

    public static function getCurrencyCodeByRegion(string $region): string
    {
        $currencyMap = [
            self::REGION_US => 'USD',
            self::REGION_GB => 'GBP',
            self::REGION_ES => 'EUR',
        ];

        return $currencyMap[$region] ?? '';
    }
}
