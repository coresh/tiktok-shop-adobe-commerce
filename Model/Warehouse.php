<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Warehouse as WarehouseResource;

class Warehouse extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const TYPE_SALES_WAREHOUSE = 1;
    public const TYPE_RETURN_WAREHOUSE = 2;

    public const SUB_TYPE_DOMESTIC_WAREHOUSE = 1;
    public const SUB_TYPE_CB_OVERSEA_WAREHOUSE = 2;
    public const SUB_TYPE_CB_DIRECT_SHIPPING_WAREHOUSE = 3;

    public const EFFECT_STATUS_EFFECTIVE = 1;
    public const EFFECT_STATUS_NONEFFECTIVE = 2;
    public const EFFECT_STATUS_RESTRICTED = 3;
    public const EFFECT_STATUS_HOLIDAY_MODE = 4;
    public const EFFECT_STATUS_ORDER_LIMIT_MODE = 5;

    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\Warehouse\ShippingMappingFactory $shippingMappingFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Warehouse\ShippingMappingFactory $shippingMappingFactory,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
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
        $this->shopRepository = $shopRepository;
        $this->shippingMappingFactory = $shippingMappingFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(WarehouseResource::class);
    }

    // ----------------------------------------

    public function setShop(Shop $shop): void
    {
        $this->shop = $shop;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getShop(): \M2E\TikTokShop\Model\Shop
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->shop)) {
            return $this->shop;
        }

        $shop = $this->shopRepository->find($this->getShopId());
        if ($shop === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Account must be created');
        }

        return $this->shop = $shop;
    }

    public function create(
        Shop $shop,
        string $warehouseId,
        string $name,
        int $effectStatus,
        int $type,
        int $subType,
        bool $isDefault,
        array $address
    ): self {
        $this->setData(WarehouseResource::COLUMN_SHOP_ID, $shop->getId())
             ->setData(WarehouseResource::COLUMN_WAREHOUSE_ID, $warehouseId)
             ->setName($name)
             ->setData(WarehouseResource::COLUMN_EFFECT_STATUS, $effectStatus)
             ->setData(WarehouseResource::COLUMN_TYPE, $type)
             ->setData(WarehouseResource::COLUMN_SUB_TYPE, $subType)
             ->setIsDefault($isDefault)
             ->setData(WarehouseResource::COLUMN_ADDRESS, json_encode($address, JSON_THROW_ON_ERROR));

        $this->setShop($shop);

        return $this;
    }

    public function getId(): ?int
    {
        if ($this->getDataByKey(WarehouseResource::COLUMN_ID) === null) {
            return null;
        }

        return (int)$this->getDataByKey(WarehouseResource::COLUMN_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getData(WarehouseResource::COLUMN_SHOP_ID);
    }

    public function getWarehouseId(): string
    {
        return $this->getData(WarehouseResource::COLUMN_WAREHOUSE_ID);
    }

    public function setName(string $name): self
    {
        $this->setData(WarehouseResource::COLUMN_NAME, $name);

        return $this;
    }

    public function getName(): string
    {
        return (string)$this->getData(WarehouseResource::COLUMN_NAME);
    }

    public function isDefault(): bool
    {
        return (bool)$this->getData(WarehouseResource::COLUMN_IS_DEFAULT);
    }

    public function setIsDefault(bool $value): self
    {
        $this->setData(WarehouseResource::COLUMN_IS_DEFAULT, (int)$value);

        return $this;
    }

    public function setShippingProviderMapping(
        \M2E\TikTokShop\Model\Warehouse\ShippingMapping $shippingMapping
    ): self {
        $json = json_encode($shippingMapping->toArray(), JSON_THROW_ON_ERROR);
        $this->setData(WarehouseResource::COLUMN_SHIPPING_PROVIDER_MAPPING, $json);

        return $this;
    }

    public function getShippingProviderMapping(): \M2E\TikTokShop\Model\Warehouse\ShippingMapping
    {
        $mapping = $this->getData(WarehouseResource::COLUMN_SHIPPING_PROVIDER_MAPPING);
        if (empty($mapping)) {
            return $this->shippingMappingFactory->create([]);
        }

        return $this->shippingMappingFactory->create(json_decode($mapping, true));
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(WarehouseResource::COLUMN_UPDATE_DATE),
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(WarehouseResource::COLUMN_CREATE_DATE),
        );
    }

    public function isTypeSales(): bool
    {
        return (int)$this->getData(WarehouseResource::COLUMN_TYPE) === self::TYPE_SALES_WAREHOUSE;
    }
}
