<?php

namespace M2E\TikTokShop\Model\ResourceModel;

class ShippingProvider extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_DELIVERY_OPTION_ID = 'tts_delivery_option_id';
    public const COLUMN_SHIPPING_PROVIDER_ID = 'tts_shipping_provider_id';
    public const COLUMN_SHIPPING_PROVIDER_NAME = 'tts_shipping_provider_name';

    private ShippingProvider\CollectionFactory $shippingProviderCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\ShippingProvider\CollectionFactory $shippingProviderCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
        $this->shippingProviderCollectionFactory = $shippingProviderCollectionFactory;
    }

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SHIPPING_PROVIDERS,
            self::COLUMN_ID
        );
    }

    /**
     * @param \M2E\TikTokShop\Model\ShippingProvider $object
     *
     * @return \M2E\TikTokShop\Model\ResourceModel\ShippingProvider
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $existedObject = $this->tryFindExistedObject($object);
        if ($existedObject !== null) {
            $object->setId($existedObject->getId());
        }

        return parent::save($object);
    }

    private function tryFindExistedObject(
        \M2E\TikTokShop\Model\ShippingProvider $object
    ): ?\M2E\TikTokShop\Model\ShippingProvider {
        $collection = $this->shippingProviderCollectionFactory->create();
        $collection->addFieldToFilter(self::COLUMN_ACCOUNT_ID, $object->getAccountId());
        $collection->addFieldToFilter(self::COLUMN_SHOP_ID, $object->getShopId());
        $collection->addFieldToFilter(self::COLUMN_WAREHOUSE_ID, $object->getWarehouseId());
        $collection->addFieldToFilter(self::COLUMN_DELIVERY_OPTION_ID, $object->getDeliveryOptionId());
        $collection->addFieldToFilter(self::COLUMN_SHIPPING_PROVIDER_ID, $object->getShippingProviderId());

        /** @var \M2E\TikTokShop\Model\ShippingProvider $existObject */
        $existObject = $collection->getFirstItem();

        if ($existObject->isObjectNew()) {
            return null;
        }

        return $existObject;
    }
}
