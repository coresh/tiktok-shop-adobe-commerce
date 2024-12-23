<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Account\Settings;

class Order
{
    public const NUMBER_SOURCE_MAGENTO = 'magento';
    public const NUMBER_SOURCE_CHANNEL = 'channel';

    public const LISTINGS_STORE_MODE_DEFAULT = 0;
    public const LISTINGS_STORE_MODE_CUSTOM = 1;

    public const LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    public const LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    public const CUSTOMER_MODE_GUEST = 0;
    public const CUSTOMER_MODE_PREDEFINED = 1;
    public const CUSTOMER_MODE_NEW = 2;

    public const USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS = 0;
    public const USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT = 1;

    public const CANCEL_ON_CHANNEL_NO = 0;
    public const CANCEL_ON_CHANNEL_YES = 1;

    public const CANCEL_ON_CHANNEL_REASON_UNABLE_DELIVER = 'unable_deliver';
    public const CANCEL_ON_CHANNEL_REASON_PRICING_ERROR = 'pricing_error';
    public const CANCEL_ON_CHANNEL_REASON_OUT_OF_STOCK = 'out_of_stock';

    public const TAX_MODE_NONE = 0;
    public const TAX_MODE_CHANNEL = 1;
    public const TAX_MODE_MAGENTO = 2;
    public const TAX_MODE_MIXED = 3;

    public const CREATE_CREDIT_MEMO_IF_ORDER_CANCELLED_NO = 0;
    public const CREATE_CREDIT_MEMO_IF_ORDER_CANCELLED_YES = 1;

    public const ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    public const ORDERS_STATUS_MAPPING_MODE_CUSTOM = 1;

    public const ORDERS_STATUS_MAPPING_PROCESSING = 'processing';
    public const ORDERS_STATUS_MAPPING_SHIPPED = 'complete';

    private array $listing = [
        'mode' => true,
        'store_mode' => self::LISTINGS_STORE_MODE_DEFAULT,
        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
    ];

    private array $listingOther = [
        'mode' => true,
        'product_mode' => self::LISTINGS_OTHER_PRODUCT_MODE_IGNORE,
        'product_tax_class_id' => \M2E\TikTokShop\Model\Magento\Product::TAX_CLASS_ID_NONE,
        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
    ];

    private array $number = [
        'source' => self::NUMBER_SOURCE_MAGENTO,
        'prefix' => [
            'prefix' => '',
        ],
    ];

    private array $shippingInformation = [
        'ship_by_date' => true,
        'shipping_address_region_override' => true,
    ];

    private array $qtyReservation = [
        'days' => 1,
    ];

    private array $orderCancelRefundOnChannel = [
        'mode' => self::CANCEL_ON_CHANNEL_YES,
        'cancel_reason' => self::CANCEL_ON_CHANNEL_REASON_UNABLE_DELIVER,
    ];

    private array $createCreditMemoIfOrderCancelled = [
        'mode' => self::CREATE_CREDIT_MEMO_IF_ORDER_CANCELLED_NO,
    ];

    private array $tax = [
        'mode' => self::TAX_MODE_MIXED,
    ];

    private array $customer = [
        'mode' => self::CUSTOMER_MODE_GUEST,
        'id' => 0,
        'website_id' => 0,
        'group_id' => 0,
        'notifications' => [
            'invoice_created' => false,
            'order_created' => false,
        ],
        'billing_address_mode' => self::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT,
    ];

    private array $orderStatusMapping = [
        'mode' => self::ORDERS_STATUS_MAPPING_MODE_DEFAULT,
        'processing' => self::ORDERS_STATUS_MAPPING_PROCESSING,
        'shipped' => self::ORDERS_STATUS_MAPPING_SHIPPED
    ];

    // ----------------------------------------

    public function isListingEnabled(): bool
    {
        return (bool)$this->listing['mode'];
    }

    public function getListingStoreMode(): int
    {
        return (int)$this->listing['store_mode'];
    }

    public function isListingStoreModeFromListing(): bool
    {
        return $this->getListingStoreMode() === self::LISTINGS_STORE_MODE_DEFAULT;
    }

    public function isListingStoreModeCustom(): bool
    {
        return $this->getListingStoreMode() === self::LISTINGS_STORE_MODE_CUSTOM;
    }

    public function getListingStoreIdForCustomMode(): int
    {
        return $this->listing['store_id'];
    }

    // ----------------------------------------

    public function isUnmanagedListingEnabled(): bool
    {
        return (bool)$this->listingOther['mode'];
    }

    public function getUnmanagedListingProductMode(): int
    {
        return (int)$this->listingOther['product_mode'];
    }

    public function isUnmanagedListingCreateProductAndOrderEnabled(): bool
    {
        return $this->getUnmanagedListingProductMode() === self::LISTINGS_OTHER_PRODUCT_MODE_IMPORT;
    }

    public function getUnmanagedListingStoreId(): int
    {
        return (int)$this->listingOther['store_id'];
    }

    public function getUnmanagedListingProductTaxClassId(): int
    {
        return $this->listingOther['product_tax_class_id'];
    }

    // ---------------------------------------

    public function getMagentoOrderNumberSource(): string
    {
        return $this->number['source'];
    }

    public function isMagentoOrdersNumberSourceMagento(): bool
    {
        return $this->getMagentoOrderNumberSource() === self::NUMBER_SOURCE_MAGENTO;
    }

    public function isMagentoOrdersNumberSourceChannel(): bool
    {
        return $this->getMagentoOrderNumberSource() === self::NUMBER_SOURCE_CHANNEL;
    }

    public function getMagentoOrdersNumberRegularPrefix(): string
    {
        return $this->number['prefix']['prefix'];
    }

    // ----------------------------------------

    public function isImportShipByDate(): bool
    {
        return (bool)$this->shippingInformation['ship_by_date'];
    }

    public function isRegionOverrideRequired(): bool
    {
        return (bool)$this->shippingInformation['shipping_address_region_override'];
    }

    // ----------------------------------------

    public function getQtyReservationDays(): int
    {
        return $this->qtyReservation['days'];
    }

    // ----------------------------------------

    public function getOrderCancelOrRefundOnChannelMode(): int
    {
        return (int)$this->orderCancelRefundOnChannel['mode'];
    }

    public function isOrderCancelOrRefundOnChannelEnabled(): bool
    {
        return $this->getOrderCancelOrRefundOnChannelMode() === self::CANCEL_ON_CHANNEL_YES;
    }

    public function getOrderCancelOnChannelReason(): string
    {
        return $this->orderCancelRefundOnChannel['cancel_reason'];
    }

    public function getCreateCreditMemoIfOrderCancelledMode(): int
    {
        return (int)$this->createCreditMemoIfOrderCancelled['mode'];
    }

    public function isCreateCreditMemoIfOrderCancelledEnabled(): bool
    {
        return $this->getCreateCreditMemoIfOrderCancelledMode() === self::CREATE_CREDIT_MEMO_IF_ORDER_CANCELLED_YES;
    }

    // ----------------------------------------

    public function getTaxMode(): int
    {
        return (int)$this->tax['mode'];
    }

    public function isTaxModeNone(): bool
    {
        return $this->getTaxMode() === self::TAX_MODE_NONE;
    }

    public function isTaxModeChannel(): bool
    {
        return $this->getTaxMode() === self::TAX_MODE_CHANNEL;
    }

    public function isTaxModeMagento(): bool
    {
        return $this->getTaxMode() === self::TAX_MODE_MAGENTO;
    }

    public function isTaxModeMixed(): bool
    {
        return $this->getTaxMode() === self::TAX_MODE_MIXED;
    }

    // ----------------------------------------

    public function getCustomerMode(): int
    {
        return (int)$this->customer['mode'];
    }

    public function isCustomerGuest(): bool
    {
        return $this->getCustomerMode() === self::CUSTOMER_MODE_GUEST;
    }

    public function isCustomerPredefined(): bool
    {
        return $this->getCustomerMode() === self::CUSTOMER_MODE_PREDEFINED;
    }

    public function getCustomerPredefinedId(): int
    {
        return $this->customer['id'];
    }

    public function isCustomerNew(): bool
    {
        return $this->getCustomerMode() === self::CUSTOMER_MODE_NEW;
    }

    public function isCustomerNewNotifyWhenOrderCreated(): bool
    {
        return $this->customer['notifications']['order_created'] ?? false;
    }

    public function isCustomerNewNotifyWhenInvoiceCreated(): bool
    {
        return $this->customer['notifications']['invoice_created'] ?? false;
    }

    public function getCustomerNewWebsiteId(): int
    {
        return $this->customer['website_id'];
    }

    public function getCustomerNewGroupId(): int
    {
        return (int)$this->customer['group_id'];
    }

    public function getCustomerBillingAddressMode(): int
    {
        return (int)$this->customer['billing_address_mode'];
    }

    // ----------------------------------------

    public function getStatusMappingMode(): int
    {
        return (int)$this->orderStatusMapping['mode'];
    }

    public function getStatusMappingForProcessing(): string
    {
        return $this->orderStatusMapping['processing'];
    }

    public function getStatusMappingForProcessingShipped(): string
    {
        return $this->orderStatusMapping['shipped'];
    }

    public function isOrderStatusMappingModeDefault(): bool
    {
        return $this->getStatusMappingMode() === self::ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    // ----------------------------------------

    public function useMagentoOrdersShippingAddressAsBillingAlways(): bool
    {
        return $this->getCustomerBillingAddressMode() === self::USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS;
    }

    public function useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient(): bool
    {
        return $this->getCustomerBillingAddressMode() === self::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT;
    }

    public function createWith(array $data): self
    {
        $new = clone $this;
        if (isset($data['listing'])) {
            $new->listing = array_merge($new->listing, $this->prepareListingData($data['listing']));
        }

        if (isset($data['listing_other'])) {
            $new->listingOther = array_merge(
                $new->listingOther,
                $this->prepareListingOtherData($data['listing_other']),
            );
        }

        if (isset($data['number'])) {
            $new->number = array_merge($new->number, $data['number']);
        }

        if (isset($data['customer'])) {
            $new->customer = array_merge($new->customer, $this->prepareCustomerData($data['customer']));
        }

        if (isset($data['order_cancel_on_channel'])) {
            $new->orderCancelRefundOnChannel = array_merge(
                $new->orderCancelRefundOnChannel,
                $this->prepareOrderCancelOnChannelData($data['order_cancel_on_channel']),
            );
        }

        if (isset($data['create_creditmemo_if_order_cancelled'])) {
            $new->createCreditMemoIfOrderCancelled = array_merge(
                $new->createCreditMemoIfOrderCancelled,
                $this->prepareCreateCreditMemoIfOrderCancelledData(
                    $data['create_creditmemo_if_order_cancelled']
                ),
            );
        }

        if (isset($data['tax'])) {
            $new->tax = array_merge($new->tax, $this->prepareTaxData($data['tax']));
        }

        if (isset($data['qty_reservation'])) {
            $new->qtyReservation = array_merge(
                $new->qtyReservation,
                $this->prepareQtyReservationData($data['qty_reservation']),
            );
        }

        if (isset($data['shipping_information'])) {
            $new->shippingInformation = array_merge(
                $new->shippingInformation,
                $this->prepareShippingInformationData($data['shipping_information']),
            );
        }

        if (isset($data['order_status_mapping'])) {
            $new->orderStatusMapping = array_merge(
                $new->orderStatusMapping,
                $this->prepareOrderStatusMappingData($data['order_status_mapping']),
            );
        }

        return $new;
    }

    public function toArray(): array
    {
        return [
            'listing' => $this->listing,
            'listing_other' => $this->listingOther,
            'number' => $this->number,
            'customer' => $this->customer,
            'order_cancel_on_channel' => $this->orderCancelRefundOnChannel,
            'create_creditmemo_if_order_cancelled' => $this->createCreditMemoIfOrderCancelled,
            'tax' => $this->tax,
            'qty_reservation' => $this->qtyReservation,
            'shipping_information' => $this->shippingInformation,
            'order_status_mapping' => $this->orderStatusMapping,
        ];
    }

    private function prepareListingData(array $listing): array
    {
        if (isset($listing['mode'])) {
            $listing['mode'] = (bool)(int)$listing['mode'];
        }

        if (isset($listing['store_mode'])) {
            $listing['store_mode'] = (int)$listing['store_mode'];
        }

        if (isset($listing['store_id'])) {
            $listing['store_id'] = (int)$listing['store_id'];
        }

        return $listing;
    }

    private function prepareListingOtherData(array $listingOther): array
    {
        if (isset($listingOther['mode'])) {
            $listingOther['mode'] = (bool)(int)$listingOther['mode'];
        }

        if (isset($listingOther['product_mode'])) {
            $listingOther['product_mode'] = (int)$listingOther['product_mode'];
        }

        if (isset($listingOther['product_tax_class_id'])) {
            $listingOther['product_tax_class_id'] = (int)$listingOther['product_tax_class_id'];
        }

        if (isset($listingOther['store_id'])) {
            $listingOther['store_id'] = (int)$listingOther['store_id'];
        }

        return $listingOther;
    }

    private function prepareShippingInformationData(array $shippingInformation): array
    {
        if (isset($shippingInformation['ship_by_date'])) {
            $shippingInformation['ship_by_date'] = (bool)(int)$shippingInformation['ship_by_date'];
        }

        if (isset($shippingInformation['shipping_address_region_override'])) {
            $shippingInformation['shipping_address_region_override'] = (bool)(int)$shippingInformation['shipping_address_region_override'];
        }

        return $shippingInformation;
    }

    private function prepareQtyReservationData(array $qtyReservation): array
    {
        if (isset($qtyReservation['days'])) {
            $qtyReservation['days'] = (int)$qtyReservation['days'];
        }

        return $qtyReservation;
    }

    private function prepareOrderCancelOnChannelData(array $orderCancelOnChannel): array
    {
        if (isset($orderCancelOnChannel['mode'])) {
            $orderCancelOnChannel['mode'] = (int)$orderCancelOnChannel['mode'];
        }

        return $orderCancelOnChannel;
    }

    private function prepareCreateCreditMemoIfOrderCancelledData(array $createCreditMemoForMagentoOrder): array
    {
        if (isset($createCreditMemoForMagentoOrder['mode'])) {
            $createCreditMemoForMagentoOrder['mode'] = (int)$createCreditMemoForMagentoOrder['mode'];
        }

        return $createCreditMemoForMagentoOrder;
    }

    private function prepareTaxData(array $tax): array
    {
        if (isset($tax['mode'])) {
            $tax['mode'] = (int)$tax['mode'];
        }

        return $tax;
    }

    private function prepareCustomerData(array $customer): array
    {
        if (isset($customer['mode'])) {
            $customer['mode'] = (int)$customer['mode'];
        }

        if (isset($customer['id'])) {
            $customer['id'] = (int)$customer['id'];
        }

        if (isset($customer['website_id'])) {
            $customer['website_id'] = (int)$customer['website_id'];
        }

        if (isset($customer['group_id'])) {
            $customer['group_id'] = (int)$customer['group_id'];
        }

        if (isset($customer['billing_address_mode'])) {
            $customer['billing_address_mode'] = (int)$customer['billing_address_mode'];
        }

        if (isset($customer['notifications'])) {
            if (
                isset($customer['notifications']['invoice_created'])
                || isset($customer['notifications']['order_created'])
            ) {
                if (isset($customer['notifications']['invoice_created'])) {
                    $customer['notifications']['invoice_created'] = (bool)(int)$customer['notifications']['invoice_created'];
                }
                if (isset($customer['notifications']['order_created'])) {
                    $customer['notifications']['order_created'] = (bool)(int)$customer['notifications']['order_created'];
                }
            } else {
                $requiredValues = [
                    'invoice_created',
                    'order_created',
                ];
                $newCustomerNotificationData = [];
                foreach ($requiredValues as $type) {
                    $newCustomerNotificationData[$type] = in_array($type, $customer['notifications']);
                }
                $customer['notifications'] = $newCustomerNotificationData;
            }
        }

        return $customer;
    }

    private function prepareOrderStatusMappingData(array $orderStatus): array
    {
        if (isset($orderStatus['mode'])) {
            if ($orderStatus['mode'] === self::ORDERS_STATUS_MAPPING_MODE_DEFAULT) {
                return $this->orderStatusMapping;
            }

            $orderStatus['mode'] = (int)$orderStatus['mode'];
        }

        if (isset($orderStatus['processing'])) {
            $orderStatus['processing'] = (string)$orderStatus['processing'];
        }

        if (isset($orderStatus['shipped'])) {
            $orderStatus['shipped'] = (string)$orderStatus['shipped'];
        }

        return $orderStatus;
    }
}
