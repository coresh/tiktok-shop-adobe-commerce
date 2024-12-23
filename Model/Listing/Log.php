<?php

namespace M2E\TikTokShop\Model\Listing;

use M2E\TikTokShop\Model\ResourceModel\Listing\Log as LogResource;

/**
 * @method \M2E\TikTokShop\Model\ResourceModel\Listing\Log getResource()
 */

/**
 * @method \M2E\TikTokShop\Model\ResourceModel\Listing\Log\Collection getCollection()
 */
class Log extends \M2E\TikTokShop\Model\Log\AbstractModel
{
    public const ACTION_UNKNOWN = 1;
    public const _ACTION_UNKNOWN = 'System';

    public const ACTION_ADD_LISTING = 2;
    public const _ACTION_ADD_LISTING = 'Add new Listing';
    public const ACTION_DELETE_LISTING = 3;
    public const _ACTION_DELETE_LISTING = /** @lang text */ 'Delete existing Listing';

    public const ACTION_ADD_PRODUCT_TO_LISTING = 4;
    public const _ACTION_ADD_PRODUCT_TO_LISTING = 'Add Product to Listing';
    public const ACTION_DELETE_PRODUCT_FROM_LISTING = 5;
    public const _ACTION_DELETE_PRODUCT_FROM_LISTING = /** @lang text */ 'Delete Product from Listing';

    public const ACTION_ADD_NEW_CHILD_LISTING_PRODUCT = 35;
    public const _ACTION_ADD_NEW_CHILD_LISTING_PRODUCT = 'Add New Child Product';

    public const ACTION_ADD_PRODUCT_TO_MAGENTO = 6;
    public const _ACTION_ADD_PRODUCT_TO_MAGENTO = 'Add new Product to Magento Store';
    public const ACTION_DELETE_PRODUCT_FROM_MAGENTO = 7;
    public const _ACTION_DELETE_PRODUCT_FROM_MAGENTO = /** @lang text */ 'Delete existing Product from Magento Store';

    public const ACTION_CHANGE_PRODUCT_PRICE = 8;
    public const _ACTION_CHANGE_PRODUCT_PRICE = 'Change of Product Price in Magento Store';
    public const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE = 9;
    public const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE = 'Change of Product Special Price in Magento Store';
    public const ACTION_CHANGE_PRODUCT_QTY = 10;
    public const _ACTION_CHANGE_PRODUCT_QTY = 'Change of Product QTY in Magento Store';
    public const ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 11;
    public const _ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 'Change of Product Stock availability in Magento Store';
    public const ACTION_CHANGE_PRODUCT_STATUS = 12;
    public const _ACTION_CHANGE_PRODUCT_STATUS = 'Change of Product status in Magento Store';

    public const ACTION_LIST_PRODUCT = 13;
    public const _ACTION_LIST_PRODUCT = 'List Product on Channel';
    public const ACTION_RELIST_PRODUCT = 14;
    public const _ACTION_RELIST_PRODUCT = 'Relist Product on Channel';
    public const ACTION_REVISE_PRODUCT = 15;
    public const _ACTION_REVISE_PRODUCT = 'Revise Product on Channel';
    public const ACTION_STOP_PRODUCT = 16;
    public const _ACTION_STOP_PRODUCT = 'Stop Product on Channel';
    public const ACTION_DELETE_PRODUCT = 24;
    public const _ACTION_DELETE_PRODUCT = 'Remove Product from Channel';
    public const ACTION_REMOVE_PRODUCT = 17;
    public const _ACTION_REMOVE_PRODUCT = 'Remove from Channel / Remove from Listing';
    public const ACTION_DELETE_AND_REMOVE_PRODUCT = 23;
    public const _ACTION_DELETE_AND_REMOVE_PRODUCT = 'Remove from Channel & Listing';
    public const ACTION_SWITCH_TO_AFN = 29;
    public const _ACTION_SWITCH_TO_AFN = 'Switching Fulfillment to AFN';
    public const ACTION_SWITCH_TO_MFN = 30;
    public const _ACTION_SWITCH_TO_MFN = 'Switching Fulfillment to MFN';
    public const ACTION_RESET_BLOCKED_PRODUCT = 32;
    public const _ACTION_RESET_BLOCKED_PRODUCT = 'Reset Incomplete Item';

    public const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE = 19;
    public const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE
        = 'Change of Product Special Price from date in Magento Store';

    public const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE = 20;
    public const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE
        = 'Change of Product Special Price to date in Magento Store';

    public const ACTION_CHANGE_CUSTOM_ATTRIBUTE = 18;
    public const _ACTION_CHANGE_CUSTOM_ATTRIBUTE = 'Change of Product Custom Attribute in Magento Store';

    public const ACTION_CHANGE_PRODUCT_TIER_PRICE = 31;
    public const _ACTION_CHANGE_PRODUCT_TIER_PRICE = 'Change of Product Tier Price in Magento Store';

    public const ACTION_MOVE_TO_LISTING = 21;
    public const _ACTION_MOVE_TO_LISTING = 'Move to another Listing';

    public const ACTION_MOVE_FROM_OTHER_LISTING = 22;
    public const _ACTION_MOVE_FROM_OTHER_LISTING = 'Move from Unmanaged Listing';

    public const ACTION_SELL_ON_ANOTHER_SITE = 33;
    public const _ACTION_SELL_ON_ANOTHER_SITE = 'Sell On Another Marketplace';

    public const ACTION_CHANNEL_CHANGE = 25;
    public const _ACTION_CHANNEL_CHANGE = 'External Change';

    public const ACTION_REMAP_LISTING_PRODUCT = 34;
    public const _ACTION_REMAP_LISTING_PRODUCT = 'Relink';

    public const ACTION_PROMOTION = 38;
    public const _ACTION_PROMOTION = 'ActionPromotion';

    public const ACTION_EDIT_LISTING_SETTINGS = 42;
    public const _ACTION_EDIT_LISTING_SETTINGS = 'Edit Listing Settings';

    private \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->magentoProductFactory = $magentoProductFactory;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Listing\Log::class);
    }

    public function createProduct(
        \M2E\TikTokShop\Model\Product $listingProduct,
        int $initiator,
        int $action,
        int $actionId,
        string $description,
        int $type
    ): self {
        \M2E\TikTokShop\Helper\Data::validateInitiator($initiator);
        $this->validateType($type);

        $this
            ->setData(LogResource::COLUMN_ACCOUNT_ID, $listingProduct->getAccount()->getId())
            ->setData(LogResource::COLUMN_SHOP_ID, $listingProduct->getShop()->getId())
            ->setData(LogResource::COLUMN_LISTING_ID, $listingProduct->getListingId())
            ->setData(LogResource::COLUMN_LISTING_TITLE, $listingProduct->getListing()->getTitle())
            ->setData(LogResource::COLUMN_PRODUCT_ID, $listingProduct->getMagentoProductId())
            ->setData(
                LogResource::COLUMN_PRODUCT_TITLE,
                $this->magentoProductFactory->create()->getNameByProductId($listingProduct->getMagentoProductId()),
            )
            ->setData(LogResource::COLUMN_LISTING_PRODUCT_ID, $listingProduct->getId())
            ->setData(LogResource::COLUMN_INITIATOR, $initiator)
            ->setData(LogResource::COLUMN_ACTION, $action)
            ->setData(LogResource::COLUMN_ACTION_ID, $actionId)
            ->setData(LogResource::COLUMN_DESCRIPTION, $description)
            ->setData(LogResource::COLUMN_TYPE, $type)
        ;

        return $this;
    }

    public function createListing(
        \M2E\TikTokShop\Model\Listing $listing,
        int $initiator,
        int $action,
        int $actionId,
        string $description,
        int $type
    ): self {
        \M2E\TikTokShop\Helper\Data::validateInitiator($initiator);
        $this->validateType($type);

        $this
            ->setData(LogResource::COLUMN_ACCOUNT_ID, $listing->getAccountId())
            ->setData(LogResource::COLUMN_SHOP_ID, $listing->getShop()->getId())
            ->setData(LogResource::COLUMN_LISTING_ID, $listing->getId())
            ->setData(LogResource::COLUMN_LISTING_TITLE, $listing->getTitle())
            ->setData(LogResource::COLUMN_INITIATOR, $initiator)
            ->setData(LogResource::COLUMN_ACTION, $action)
            ->setData(LogResource::COLUMN_ACTION_ID, $actionId)
            ->setData(LogResource::COLUMN_DESCRIPTION, $description);

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Log::COLUMN_ACCOUNT_ID);
    }

    public function getShopId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Log::COLUMN_SHOP_ID);
    }

    public function getListingId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Log::COLUMN_LISTING_ID);
    }

    public function getListingProductId(): ?int
    {
        $value = $this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Log::COLUMN_LISTING_PRODUCT_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    public function getActionId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Log::COLUMN_ACTION_ID);
    }
}
