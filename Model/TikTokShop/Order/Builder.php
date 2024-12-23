<?php

namespace M2E\TikTokShop\Model\TikTokShop\Order;

use M2E\TikTokShop\Model\ResourceModel\Order as OrderResource;

class Builder extends \Magento\Framework\DataObject
{
    public const STATUS_NOT_MODIFIED = 0;
    public const STATUS_NEW = 1;
    public const STATUS_UPDATED = 2;
    public const STATUS_HAS_UPDATED = 3;

    private const UPDATE_BUYER_CANCELLATION_REQUEST = 'update_buyer_cancellation_request';
    private const UPDATE_BUYER_REQUEST_RETURN_FOR_ITEM = 'update_buyer_request_return_for_item';
    private const UPDATE_CANCELLATION_WAS_CONFIRMED_ON_CHANNEL = 'cancellation_was_confirmed_on_channel';

    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\Order $order;
    private int $status = self::STATUS_NOT_MODIFIED;
    private array $items = [];
    private array $updates = [];

    private array $orderLogsForOrderItems = [];
    private array $magentoOrderNotesForOrderItems = [];

    // ----------------------------------------

    private \M2E\TikTokShop\Model\Magento\Order\Updater $magentoOrderUpdater;
    private \M2E\TikTokShop\Model\TikTokShop\Order\StatusResolver $statusResolver;
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Order\Item\BuilderFactory $orderItemBuilderFactory;
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Order\AddressParserFactory $addressParserFactory;
    private \M2E\TikTokShop\Model\Order\CreditMemo $creditMemo;

    public function __construct(
        AddressParserFactory $addressParserFactory,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\TikTokShop\Order\Item\BuilderFactory $orderItemBuilderFactory,
        \M2E\TikTokShop\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\TikTokShop\Model\TikTokShop\Order\StatusResolver $statusResolver,
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\Order\CreditMemo $creditMemo
    ) {
        parent::__construct();
        $this->orderFactory = $orderFactory;
        $this->statusResolver = $statusResolver;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->orderItemBuilderFactory = $orderItemBuilderFactory;
        $this->orderRepository = $orderRepository;
        $this->addressParserFactory = $addressParserFactory;
        $this->creditMemo = $creditMemo;
    }

    public function initialize(
        \M2E\TikTokShop\Model\Shop $shop,
        array $data = []
    ): void {
        $this->account = $shop->getAccount();
        $this->shop = $shop;

        $this->initializeData($data);
        $this->initializeOrder();
    }

    private function initializeData(array $data): void
    {
        $this->setData(OrderResource::COLUMN_ACCOUNT_ID, $this->shop->getAccountId());
        $this->setData(OrderResource::COLUMN_SHOP_ID, $this->shop->getId());

        $this->setData(OrderResource::COLUMN_TTS_ORDER_ID, $data['id']);
        $this->setData(
            OrderResource::COLUMN_ORDER_STATUS,
            $this->statusResolver->resolve($data['status'])
        );

        $this->setData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST, $data['is_buyer_request_cancel']);
        $this->setData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST_REASON, $data['cancel_reason']);

        $this->setData(OrderResource::COLUMN_WAREHOUSE_ID, $data['warehouse_id']);

        $this->setData(OrderResource::COLUMN_PURCHASE_UPDATE_DATE, $data['update_date']);
        $this->setData(OrderResource::COLUMN_PURCHASE_CREATE_DATE, $data['create_date']);

        $this->setData(OrderResource::COLUMN_PAID_AMOUNT, (float)$data['payment']['total_amount']);
        $this->setData(OrderResource::COLUMN_CURRENCY, $data['payment']['currency']);

        // Tax
        $productTaxAmount = (float)($data['payment']['product_tax'] ?? 0.0);
        $shippingTaxAmount = (float)($data['payment']['shipping_fee_tax'] ?? 0.0);
        $totalTaxAmount = (float)($data['payment']['tax'] ?? 0.0);

        $taxDetails = [
            'product_amount' => $productTaxAmount,
            'shipping_amount' => $shippingTaxAmount,
            'total_amount' => $totalTaxAmount,
        ];

        $this->setData(OrderResource::COLUMN_TAX_DETAILS, \M2E\TikTokShop\Helper\Json::encode($taxDetails));

        // Buyer
        $this->setData(OrderResource::COLUMN_BUYER_USER_ID, trim($data['user_id']));
        $this->setData(OrderResource::COLUMN_BUYER_NAME, trim($data['recipient_address']['name'] ?? ''));
        $this->setData(OrderResource::COLUMN_BUYER_EMAIL, trim($data['buyer_email']));
        $this->setData(OrderResource::COLUMN_BUYER_MESSAGE, $data['buyer_message']);

        // Payment
        $this->setData(OrderResource::COLUMN_PAYMENT_METHOD_NAME, $data['payment_method_name']);
        $this->setData(OrderResource::COLUMN_PAYMENT_DATE, $data['paid_date']);

        $paymentDetails = [];
        $paymentDetails['original_total_product_price'] = $data['payment']['original_total_product_price'];
        $paymentDetails['platform_discount'] = $data['payment']['platform_discount'];
        $paymentDetails['seller_discount'] = $data['payment']['seller_discount'];

        $paymentDetails['shipping_fee'] = $data['payment']['shipping_fee'];
        $paymentDetails['original_shipping_fee'] = $data['payment']['original_shipping_fee'];
        $paymentDetails['shipping_fee_platform_discount'] = $data['payment']['shipping_fee_platform_discount'];
        $paymentDetails['shipping_fee_seller_discount'] = $data['payment']['shipping_fee_seller_discount'];

        $paymentDetails['sub_total'] = $data['payment']['sub_total'];

        $this->setData(OrderResource::COLUMN_PAYMENT_DETAILS, \M2E\TikTokShop\Helper\Json::encode($paymentDetails));

        // Shipping
        $addressParser = $this->addressParserFactory->create($this->shop->getRegion(), $data);
        $shippingDetails = [
            'price' => $data['payment']['shipping_fee'],
            'service' => $data['delivery_option_name'],
            'address_detail' => $data['recipient_address']['address_detail'],
            'district' => $addressParser->getDistricts(),
            'address' => [
                'buyer_name' => $addressParser->getBuyerName(),
                'buyer_email' => $addressParser->getBuyerEmail(),
                'recipient_name' => $addressParser->getRecipientName(),
                'postal_code' => $addressParser->getPostalCode(),
                'country_code' => $addressParser->getCountryCode(),
                'state' => $addressParser->getState(),
                'city' => $addressParser->getCity(),
                'street' => $addressParser->getStreetLines(),
                'phone' => $addressParser->getPhone(),
            ],
        ];

        $this->setData(OrderResource::COLUMN_SHIPPING_DETAILS, json_encode($shippingDetails));

        $this->setData(OrderResource::COLUMN_SHIPPING_DATE_TO, $data['delivery_sla_date']);

        // ---------------------------------------
        $this->items = $data['items'];
    }

    private function initializeOrder(): void
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrder = $this->getExistedOrder();

        if ($existOrder === null) {
            $this->status = self::STATUS_NEW;
            $this->order = $this->orderFactory->create();
            $this->order->markStatusUpdateRequired();

            return;
        }

        $this->order = $existOrder;
        $this->status = self::STATUS_UPDATED;
        $this->order->markStatusUpdateRequired();
    }

    private function getExistedOrder(): ?\M2E\TikTokShop\Model\Order
    {
        return $this->orderRepository->findOneByAccountIdAndTtsOrderId(
            $this->account->getId(),
            (string)$this->getData('tts_order_id')
        );
    }

    // ----------------------------------------

    public function process(): ?\M2E\TikTokShop\Model\Order
    {
        if (!$this->canCreateOrUpdateOrder()) {
            return null;
        }

        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        $this->checkOrderItemUpdates();

        if ($this->isNew()) {
            $this->processNew();
        }

        if ($this->isUpdated()) {
            $this->processOrderUpdates();
            $this->processMagentoOrderUpdates();
        }

        return $this->order;
    }

    // ----------------------------------------

    private function createOrUpdateItems(): void
    {
        $this->order->resetItems();
        foreach ($this->items as $orderItemData) {
            $itemBuilder = $this->orderItemBuilderFactory->create();

            $item = $itemBuilder->create($orderItemData, (int)$this->order->getId());
            $item->setOrder($this->order);
        }
    }

    // ---------------------------------------

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isUpdated(): bool
    {
        return $this->status === self::STATUS_UPDATED;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Exception
     */
    private function canCreateOrUpdateOrder(): bool
    {
        if ($this->order->getId()) {
            $newPurchaseUpdateDate = \M2E\TikTokShop\Helper\Date::createDateGmt(
                $this->getData('purchase_update_date')
            );
            $oldPurchaseUpdateDate = \M2E\TikTokShop\Helper\Date::createDateGmt(
                $this->order->getPurchaseUpdateDate()
            );

            if ($oldPurchaseUpdateDate > $newPurchaseUpdateDate) {
                return false;
            }
        }

        return true;
    }

    private function createOrUpdateOrder(): void
    {
        foreach ($this->getData() as $key => $value) {
            if (
                !$this->order->getId()
                || ($this->order->hasData($key) && $this->order->getData($key) != $value)
            ) {
                $this->order->addData($this->getData());

                $this->orderRepository->save($this->order);

                break;
            }
        }

        $this->order->setAccount($this->account);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function processNew(): void
    {
        if (!$this->isNew()) {
            return;
        }

        $tikTokAccount = $this->account;

        if (
            $this->order->hasListingProductItems()
            && !$tikTokAccount->getOrdersSettings()->isListingEnabled()
        ) {
            return;
        }

        if (
            $this->order->hasOtherListingItems()
            && !$tikTokAccount->getOrdersSettings()->isUnmanagedListingEnabled()
        ) {
            return;
        }

        if (!$this->order->canCreateMagentoOrder()) {
            $this->order->addWarningLog(
                'Magento Order was not created. Reason: %msg%',
                [
                    'msg' => 'Order Creation Rules were not met.',
                ]
            );
        }
    }

    private function checkUpdates(): void
    {
        if (!$this->isUpdated()) {
            return;
        }

        if ($this->getData(OrderResource::COLUMN_ORDER_STATUS) !== $this->order->getOrderStatus()) {
            $this->updates[] = self::STATUS_HAS_UPDATED;
        }

        if (
            $this->getData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST)
            && !$this->order->isBuyerCancellationRequest()
        ) {
            $this->updates[] = self::UPDATE_BUYER_CANCELLATION_REQUEST;
        }

        if ($this->getData(OrderResource::COLUMN_ORDER_STATUS) === \M2E\TikTokShop\Model\Order::STATUS_CANCELED) {
            $this->updates[] = self::UPDATE_CANCELLATION_WAS_CONFIRMED_ON_CHANNEL;
        }
    }

    private function checkOrderItemUpdates()
    {
        foreach ($this->order->getItems() as $orderItem) {
            if ($orderItem->isChangedColumnBuyerRequestRefundReturn()) {
                $this->updates[] = self::UPDATE_BUYER_REQUEST_RETURN_FOR_ITEM;

                $message = (string)__(
                    'Customer has requested return of the Item %name. Return reason: %reason.',
                    ['name' => $orderItem->getChannelProductTitle(), 'reason' => $orderItem->getCancelReason()]
                );

                $this->orderLogsForOrderItems[$orderItem->getMagentoProductId()] = $message;
                $this->magentoOrderNotesForOrderItems[$orderItem->getMagentoProductId()] = $message;
            }
        }
    }

    private function hasUpdates(): bool
    {
        return !empty($this->updates);
    }

    private function hasUpdate($update): bool
    {
        return in_array($update, $this->updates);
    }

    private function processOrderUpdates(): void
    {
        if (!$this->hasUpdates()) {
            return;
        }

        if ($this->hasUpdate(self::STATUS_HAS_UPDATED)) {
            $this->order->addSuccessLog(
                sprintf(
                    'Order status was updated to %s on TikTok Shop',
                    \M2E\TikTokShop\Model\Order::getStatusTitle($this->order->getOrderStatus())
                )
            );
        }

        if (
            $this->hasUpdate(self::UPDATE_CANCELLATION_WAS_CONFIRMED_ON_CHANNEL)
            && $this->order->getAccount()->getOrdersSettings()->isCreateCreditMemoIfOrderCancelledEnabled()
        ) {
            $this->creditMemo->process($this->order);
        }

        if (
            $this->hasUpdate(self::UPDATE_BUYER_REQUEST_RETURN_FOR_ITEM)
            && !empty($this->orderLogsForOrderItems)
        ) {
            foreach ($this->orderLogsForOrderItems as $logText) {
                $this->order->addInfoLog($logText);
            }
        }
    }

    private function processMagentoOrderUpdates(): void
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $magentoOrder = $this->order->getMagentoOrder();
        if ($magentoOrder === null) {
            return;
        }

        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);
        $magentoOrderUpdater->updateStatus($this->order->getStatusForMagentoOrder());

        if ($this->hasUpdate(self::UPDATE_BUYER_CANCELLATION_REQUEST)) {
            $magentoOrderUpdater->updateComments(['Buyer requested Order Cancellation.']);
        }

        if (
            $this->hasUpdate(self::UPDATE_BUYER_REQUEST_RETURN_FOR_ITEM)
            && !empty($this->magentoOrderNotesForOrderItems)
        ) {
            $magentoOrderUpdater->updateComments(array_values($this->magentoOrderNotesForOrderItems));
        }

        $proxy = $this->order->getProxy();
        $proxy->setStore($this->order->getStore());

        $magentoOrderUpdater->finishUpdate();
    }
}
