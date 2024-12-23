<?php

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\Log\AbstractModel as Log;
use M2E\TikTokShop\Model\ResourceModel\Order as OrderResource;

class Order extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const ADDITIONAL_DATA_KEY_IN_ORDER = 'tiktokshop_order';

    public const MAGENTO_ORDER_CREATION_FAILED_YES = 1;
    public const MAGENTO_ORDER_CREATION_FAILED_NO = 0;

    public const STATUS_UNKNOWN = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_UNSHIPPED = 2;
    public const STATUS_SHIPPED = 3;
    public const STATUS_CANCELED = 4;

    private $statusUpdateRequired = false;

    /** @var float|int|null */
    private $subTotalPrice = null;
    private ?float $grandTotalPrice = null;

    /** @var \M2E\TikTokShop\Model\Order\Item[] */
    private array $items;
    private ?\Magento\Sales\Model\Order $magentoOrder = null;
    private ?Order\ShippingAddress $shippingAddress = null;
    private ?Account $account = null;
    private ?Order\ProxyObject $proxy = null;
    private ?Order\Reserve $reserve = null;
    private ?\M2E\TikTokShop\Model\Order\Log\Service $logService = null;
    private ?ResourceModel\Order\Item\Collection $itemsCollection = null;

    // ----------------------------------------

    private \M2E\TikTokShop\Model\Magento\Quote\Manager $quoteManager;
    private \M2E\TikTokShop\Model\Magento\Quote\BuilderFactory $magentoQuoteBuilderFactory;
    private \M2E\TikTokShop\Model\Magento\Order\Updater $magentoOrderUpdater;
    private \M2E\TikTokShop\Model\Magento\Order\ShipmentFactory $shipmentFactory;
    private \M2E\TikTokShop\Model\Magento\Order\Shipment\TrackFactory $magentoOrderShipmentTrackFactory;
    private \M2E\TikTokShop\Model\Magento\Order\Invoice $magentoOrderInvoice;

    private \Magento\Store\Model\StoreManager $storeManager;
    private \Magento\Sales\Model\OrderFactory $orderFactory;

    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\TikTokShop\Helper\Module\Exception $helperModuleException;

    private \Magento\Catalog\Helper\Product $productHelper;
    private Shop\Repository $shopRepository;
    private \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender;
    private \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender;
    private \M2E\TikTokShop\Model\Order\ProxyObjectFactory $proxyObjectFactory;
    private TikTokShop\Order\ShippingAddressFactory $shippingAddressFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\TikTokShop\Model\Order\Log\ServiceFactory $orderLogServiceFactory;
    private \M2E\TikTokShop\Model\Order\ReserveFactory $orderReserveFactory;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\TikTokShop\Helper\Module\Logger $loggerHelper;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\TikTokShop\Helper\Magento\Store $magentoStoreHelper;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;
    private Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Model\Order\EventDispatcher $orderEventDispatcher;

    public function __construct(
        \M2E\TikTokShop\Model\Order\EventDispatcher $orderEventDispatcher,
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Magento\Quote\Manager $quoteManager,
        \M2E\TikTokShop\Model\Magento\Quote\BuilderFactory $magentoQuoteBuilderFactory,
        \M2E\TikTokShop\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\TikTokShop\Model\Magento\Order\ShipmentFactory $shipmentFactory,
        \M2E\TikTokShop\Model\Magento\Order\Shipment\TrackFactory $magentoOrderShipmentTrackFactory,
        \M2E\TikTokShop\Model\Magento\Order\Invoice $magentoOrderInvoice,
        \M2E\TikTokShop\Model\Order\ReserveFactory $orderReserveFactory,
        \M2E\TikTokShop\Model\Order\Log\ServiceFactory $orderLogServiceFactory,
        \M2E\TikTokShop\Model\Order\ProxyObjectFactory $proxyObjectFactory,
        \M2E\TikTokShop\Model\TikTokShop\Order\ShippingAddressFactory $shippingAddressFactory,
        Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Helper\Magento\Store $magentoStoreHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Helper\Module\Logger $loggerHelper,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Helper\Module\Exception $helperModuleException,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Helper\Product $productHelper,
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
            $data
        );

        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productHelper = $productHelper;
        $this->quoteManager = $quoteManager;
        $this->helperModuleException = $helperModuleException;
        $this->shopRepository = $shopRepository;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->proxyObjectFactory = $proxyObjectFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->shippingAddressFactory = $shippingAddressFactory;
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->orderLogServiceFactory = $orderLogServiceFactory;
        $this->orderReserveFactory = $orderReserveFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->loggerHelper = $loggerHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->magentoQuoteBuilderFactory = $magentoQuoteBuilderFactory;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->magentoOrderShipmentTrackFactory = $magentoOrderShipmentTrackFactory;
        $this->magentoOrderInvoice = $magentoOrderInvoice;
        $this->accountRepository = $accountRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderEventDispatcher = $orderEventDispatcher;
    }

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(OrderResource::class);
    }

    public static function getStatusTitle(int $status): string
    {
        $statuses = [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_UNSHIPPED => __('Unshipped'),
            self::STATUS_SHIPPED => __('Shipped'),
            self::STATUS_CANCELED => __('Canceled'),
            self::STATUS_UNKNOWN => __('Unknown'),
        ];

        return (string)($statuses[$status] ?? __('Unknown'));
    }

    //########################################

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        $orderNoteCollection = $this->orderNoteCollectionFactory->create();
        $orderNoteCollection->addFieldToFilter('order_id', $this->getId());
        foreach ($orderNoteCollection->getItems() as $orderNote) {
            $orderNote->delete();
        }

        foreach ($this->getItems() as $item) {
            $item->delete();
        }

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->addFieldToFilter('order_id', $this->getId());
        foreach ($orderChangeCollection->getItems() as $orderChange) {
            $orderChange->delete();
        }

        $this->account = null;
        $this->magentoOrder = null;
        $this->itemsCollection = null;
        $this->proxy = null;

        return parent::delete();
    }

    //########################################

    public function getId(): ?int
    {
        $orderId = parent::getId();
        if ($orderId === null) {
            return null;
        }

        return $orderId;
    }

    public function resetItems(): void
    {
        unset($this->items);
        $this->itemsCollection = null;
    }

    public function getItems(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->items)) {
            return $this->items;
        }

        return $this->items = $this->orderItemRepository->findByOrder($this);
    }

    public function getItem(int $itemId): \M2E\TikTokShop\Model\Order\Item
    {
        $item = $this->findItem($itemId);
        if ($item === null) {
            throw new \LogicException("Order item with id '$itemId' does not exist");
        }

        return $item;
    }

    public function findItem(int $itemId): ?\M2E\TikTokShop\Model\Order\Item
    {
        foreach ($this->getItems() as $item) {
            if ($item->getId() === $itemId) {
                return $item;
            }
        }

        return null;
    }

    public function getMagentoOrderCreationLatestAttemptDate()
    {
        return $this->getData('magento_order_creation_latest_attempt_date');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getReservationState(): int
    {
        return (int)$this->getData('reservation_state');
    }

    public function getReservationStartDate(): string
    {
        return (string)$this->getData('reservation_start_date');
    }

    public function getShop(): \M2E\TikTokShop\Model\Shop
    {
        return $this->shopRepository->get($this->getShopId());
    }

    public function getShopId(): int
    {
        return (int)$this->getData('shop_id');
    }

    public function getWarehouse(): Warehouse
    {
        return $this->warehouseRepository->getByWarehouseId($this->getWarehouseId());
    }

    public function getWarehouseId(): string
    {
        return (string)$this->getData('warehouse_id');
    }

    public function getTtsOrderId(): string
    {
        return (string)$this->getData('tts_order_id');
    }

    /**
     * Check whether the order has items, listed by M2E TikTok Shop (also true for linked Unmanaged listings)
     */
    public function hasListingProductItems(): bool
    {
        return !empty($this->getListingProductVariantSkus());
    }

    /**
     * @return \M2E\TikTokShop\Model\Product\VariantSku[]
     */
    public function getListingProductVariantSkus(): array
    {
        $listingProducts = [];
        foreach ($this->getItems() as $item) {
            $variantSku = $item->getVariantSku();

            if ($variantSku === null) {
                continue;
            }

            $listingProducts[] = $variantSku;
        }

        return $listingProducts;
    }

    /**
     * Check whether the order has items, listed by Unmanaged software
     */
    public function hasOtherListingItems(): bool
    {
        return count($this->getListingProductVariantSkus()) !== count($this->getItems());
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function isMagentoShipmentCreatedByOrder(\Magento\Sales\Model\Order\Shipment $magentoShipment): bool
    {
        $additionalData = $this->getAdditionalData();
        if (empty($additionalData['created_shipments_ids']) || !is_array($additionalData['created_shipments_ids'])) {
            return false;
        }

        return in_array($magentoShipment->getId(), $additionalData['created_shipments_ids']);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getAdditionalData(): array
    {
        return $this->getSettings('additional_data');
    }

    //########################################

    public function canCreateMagentoOrder(): bool
    {
        if ($this->getMagentoOrderId() !== null) {
            return false;
        }

        if ($this->isCanceled()) {
            return false;
        }

        if ($this->isStatusPending()) {
            return false;
        }

        foreach ($this->getItems() as $item) {
            if (!$item->canCreateMagentoOrder()) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function getMagentoOrderId()
    {
        return $this->getData('magento_order_id');
    }

    //########################################

    public function isCanceled(): bool
    {
        return $this->getOrderStatus() === self::STATUS_CANCELED;
    }

    public function canCancel(): bool
    {
        if ($this->isCanceled()) {
            return false;
        }

        return true;
    }

    //region Order status
    public function setOrderStatus(int $status): void
    {
        $this->setData(OrderResource::COLUMN_ORDER_STATUS, $status);
    }

    public function getOrderStatus(): int
    {
        return (int)($this->getData(OrderResource::COLUMN_ORDER_STATUS) ?? 0);
    }

    public function isStatusPending(): bool
    {
        return $this->getOrderStatus() === self::STATUS_PENDING;
    }

    public function isStatusCanceled(): bool
    {
        return $this->getOrderStatus() === self::STATUS_CANCELED;
    }

    public function isStatusShipping(): bool
    {
        return $this->getOrderStatus() === self::STATUS_SHIPPED;
    }

    public function isStatusUnshipping(): bool
    {
        return $this->getOrderStatus() === self::STATUS_UNSHIPPED;
    }
    //endregion

    // ---------------------------------------

    /**
     * @throws \Throwable
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \M2E\TikTokShop\Model\Magento\Quote\FailDuringEventProcessing
     * @throws \M2E\TikTokShop\Model\Order\Exception\ProductCreationDisabled
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function createMagentoOrder(): void
    {
        try {
            // Check if we are wrapped by an another MySql transaction
            // ---------------------------------------
            $connection = $this->resourceConnection->getConnection();
            if ($transactionLevel = $connection->getTransactionLevel()) {
                $this->loggerHelper->process(
                    ['transaction_level' => $transactionLevel],
                    'MySql Transaction Level Problem'
                );

                while ($connection->getTransactionLevel()) {
                    $connection->rollBack();
                }
            }
            // ---------------------------------------

            /**
             *  Since version 2.1.8 Magento added check if product is saleable before creating quote.
             *  When order is creating from back-end, this check is skipped. See example at
             *  Magento\Sales\Controller\Adminhtml\Order\Create.php
             */
            $this->productHelper->setSkipSaleableCheck(true);

            // Store must be initialized before products
            // ---------------------------------------
            $this->associateWithStore();
            $this->associateItemsWithProducts();
            // ---------------------------------------

            $this->beforeCreateMagentoOrder();

            // Create magento order
            // ---------------------------------------
            $proxyOrder = $this->getProxy();
            $proxyOrder->setStore($this->getStore());

            $magentoQuoteBuilder = $this->magentoQuoteBuilderFactory->create($proxyOrder);
            $magentoQuote = $magentoQuoteBuilder->build();

            $this->globalDataHelper->unsetValue(self::ADDITIONAL_DATA_KEY_IN_ORDER);
            $this->globalDataHelper->setValue(self::ADDITIONAL_DATA_KEY_IN_ORDER, $this);

            try {
                $this->magentoOrder = $this->quoteManager->submit($magentoQuote);
            } catch (\M2E\TikTokShop\Model\Magento\Quote\FailDuringEventProcessing $e) {
                $this->addWarningLog(
                    'Magento Order was created.
                     However one or more post-processing actions on Magento Order failed.
                     This may lead to some issues in the future.
                     Please check the configuration of the ancillary services of your Magento.
                     For more details, read the original Magento warning: %msg%.',
                    [
                        'msg' => $e->getMessage(),
                    ]
                );
                $this->magentoOrder = $e->getOrder();
            }

            $magentoOrderId = $this->getMagentoOrderId();

            if (empty($magentoOrderId)) {
                $now = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
                $this->addData([
                    'magento_order_id' => $this->magentoOrder->getId(),
                    'magento_order_creation_failure' => self::MAGENTO_ORDER_CREATION_FAILED_NO,
                    'magento_order_creation_latest_attempt_date' => $now,
                ]);

                $this->setMagentoOrder($this->magentoOrder);
                $this->save();
            }

            $this->afterCreateMagentoOrder();
            unset($magentoQuoteBuilder);
        } catch (\Throwable $exception) {
            unset($magentoQuoteBuilder);
            $this->globalDataHelper->unsetValue(self::ADDITIONAL_DATA_KEY_IN_ORDER);

            /**
             * \Magento\CatalogInventory\Model\StockManagement::registerProductsSale()
             * could open an transaction and may does not
             * close it in case of Exception. So all the next changes may be lost.
             */
            $connection = $this->resourceConnection->getConnection();
            if ($transactionLevel = $connection->getTransactionLevel()) {
                $this->loggerHelper->process(
                    [
                        'transaction_level' => $transactionLevel,
                        'error' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                    ],
                    'MySql Transaction Level Problem'
                );

                while ($connection->getTransactionLevel()) {
                    $connection->rollBack();
                }
            }

            $this->_eventManager->dispatch('m2e_tts_order_place_failure', ['order' => $this]);

            $now = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
            $this->addData([
                'magento_order_creation_failure' => self::MAGENTO_ORDER_CREATION_FAILED_YES,
                'magento_order_creation_fails_count' => $this->getMagentoOrderCreationFailsCount() + 1,
                'magento_order_creation_latest_attempt_date' => $now,
            ]);
            $this->save();

            $message = 'Magento Order was not created. Reason: %msg%';
            if ($exception instanceof \M2E\TikTokShop\Model\Order\Exception\ProductCreationDisabled) {
                $this->addInfoLog($message, ['msg' => $exception->getMessage()], [], true);
            } else {
                $this->exceptionHelper->process($exception);
                $this->addErrorLog($message, ['msg' => $exception->getMessage()]);
            }

            if ($this->isReservable()) {
                $this->getReserve()->place();
            }

            throw $exception;
        }
    }

    // ---------------------------------------

    /**
     * Find the store, where order should be placed
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function associateWithStore()
    {
        $storeId = $this->getStoreId() ? $this->getStoreId() : $this->getAssociatedStoreId();
        $store = $this->storeManager->getStore($storeId);

        if ($store->getId() === null) {
            throw new \M2E\TikTokShop\Model\Exception('Store does not exist.');
        }

        if ($this->getStoreId() != $store->getId()) {
            $this->setData('store_id', $store->getId())->save();
        }

        if (!$store->getConfig('payment/tiktokshoppayment/active')) {
            throw new \M2E\TikTokShop\Model\Exception(
                'Payment method "M2E TikTok Shop Connect Payment" is disabled under
                <i>Stores > Settings > Configuration > Sales > Payment Methods > M2E TikTok Shop Connect Payment.</i>'
            );
        }

        if (!$store->getConfig('carriers/tiktokshopshipping/active')) {
            throw new \M2E\TikTokShop\Model\Exception(
                'Shipping method "M2E TikTok Shop Connect Shipping" is disabled under
                <i>Stores > Settings > Configuration > Sales > Shipping Methods > M2E TikTok Shop Connect Shipping.</i>'
            );
        }
    }

    public function getStoreId(): int
    {
        return (int)$this->getData('store_id');
    }

    //########################################

    public function getAssociatedStoreId(): ?int
    {
        $productVariantSkus = $this->getListingProductVariantSkus();

        if (empty($productVariantSkus)) {
            $storeId = $this->getAccount()->getOrdersSettings()->getUnmanagedListingStoreId();
        } else {
            $firstProductVariantSku = reset($productVariantSkus);
            if ($this->getAccount()->getOrdersSettings()->isListingStoreModeCustom()) {
                $storeId = $this->getAccount()->getOrdersSettings()->getListingStoreIdForCustomMode();
            } else {
                $storeId = $firstProductVariantSku->getListing()->getStoreId();
            }
        }

        if ($storeId == 0) {
            $storeId = $this->magentoStoreHelper->getDefaultStoreId();
        }

        return $storeId;
    }

    /**
     * @return \M2E\TikTokShop\Model\Account
     */
    public function getAccount(): Account
    {
        if ($this->account === null) {
            $this->account = $this->accountRepository->get($this->getAccountId());
        }

        return $this->account;
    }

    public function setAccount(\M2E\TikTokShop\Model\Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore(): \Magento\Store\Api\Data\StoreInterface
    {
        return $this->storeManager->getStore($this->getStoreId());
    }

    /**
     * Associate each order item with product in magento
     */
    public function associateItemsWithProducts(): void
    {
        foreach ($this->getItems() as $item) {
            $item->associateWithProduct();
        }
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function beforeCreateMagentoOrder()
    {
        if ($this->getMagentoOrderId() !== null) {
            throw new \M2E\TikTokShop\Model\Exception('Magento Order is already created.');
        }

        $reserve = $this->getReserve();

        if ($reserve->isPlaced()) {
            $reserve->setFlag('order_reservation', true);
            $reserve->release();
        }
    }

    //########################################

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getShippingDetails(): array
    {
        return $this->getSettings('shipping_details');
    }

    public function getReserve(): ?\M2E\TikTokShop\Model\Order\Reserve
    {
        if ($this->reserve === null) {
            $this->reserve = $this->orderReserveFactory->create($this);
        }

        return $this->reserve;
    }

    //########################################

    public function getProxy(): Order\ProxyObject
    {
        if ($this->proxy === null) {
            $this->proxy = $this->proxyObjectFactory->create($this);
        }

        return $this->proxy;
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function addWarningLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_WARNING,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function addLog(
        $description,
        $type,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        $log = $this->getLogService();

        if (!empty($params)) {
            $description = \M2E\TikTokShop\Helper\Module\Log::encodeDescription($description, $params, $links);
        }

        return $log->addMessage(
            $this,
            $description,
            $type,
            $additionalData,
            $isUnique
        );
    }

    //########################################

    public function getLogService(): \M2E\TikTokShop\Model\Order\Log\Service
    {
        if (!$this->logService) {
            $this->logService = $this->orderLogServiceFactory->create();
        }

        return $this->logService;
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function afterCreateMagentoOrder()
    {
        // add history comments
        // ---------------------------------------
        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateComments($this->getProxy()->getComments());
        $magentoOrderUpdater->finishUpdate();
        // ---------------------------------------

        $this->orderEventDispatcher->dispatchEventsMagentoOrderCreated($this);

        $this->addSuccessLog('Magento Order #%order_id% was created.', [
            '!order_id' => $this->getMagentoOrder()->getRealOrderId(),
        ]);

        if ($this->getAccount()->getOrdersSettings()->isCustomerNewNotifyWhenOrderCreated()) {
            $this->orderSender->send($this->getMagentoOrder());
        }
    }

    public function getMagentoOrder(): ?\Magento\Sales\Model\Order
    {
        if ($this->getMagentoOrderId() === null) {
            return null;
        }

        if ($this->magentoOrder === null) {
            $this->magentoOrder = $this->orderFactory->create()->load($this->getMagentoOrderId());
        }

        return $this->magentoOrder->getId() !== null ? $this->magentoOrder : null;
    }

    public function setMagentoOrder(\Magento\Sales\Model\Order $order): self
    {
        $this->magentoOrder = $order;

        return $this;
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function addSuccessLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_SUCCESS,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    public function getMagentoOrderCreationFailsCount(): int
    {
        return (int)$this->getData('magento_order_creation_fails_count');
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function addInfoLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_INFO,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function addErrorLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_ERROR,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    public function isReservable(): bool
    {
        if ($this->getMagentoOrderId() !== null) {
            return false;
        }

        if ($this->getReserve()->isPlaced()) {
            return false;
        }

        if ($this->isCanceled()) {
            return false;
        }

        foreach ($this->getItems() as $item) {
            if (!$item->isReservable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function createInvoice(): ?\Magento\Sales\Model\Order\Invoice
    {
        $invoice = null;

        try {
            if (!$this->canCreateInvoice()) {
                return null;
            }

            $magentoOrder = $this->getMagentoOrder();

            $invoiceBuilder = $this->magentoOrderInvoice;
            $invoiceBuilder->setMagentoOrder($magentoOrder);
            $invoiceBuilder->buildInvoice();

            $invoice = $invoiceBuilder->getInvoice();

            if ($this->getAccount()->getOrdersSettings()->isCustomerNewNotifyWhenInvoiceCreated()) {
                $this->invoiceSender->send($invoice);
            }
        } catch (\Throwable $throwable) {
            $this->helperModuleException->process($throwable);
            $this->addErrorLog(
                'Invoice was not created. Reason: %msg%',
                ['msg' => $throwable->getMessage()]
            );
        }

        if ($invoice !== null) {
            $this->addSuccessLog(
                'Invoice #%invoice_id% was created.',
                ['!invoice_id' => $invoice->getIncrementId()]
            );

            $this->orderEventDispatcher->dispatchEventInvoiceCreated($this);
        }

        return $invoice;
    }

    public function canCreateInvoice(): bool
    {
        if ($this->isStatusPending()) {
            return false;
        }

        if (!$this->getAccount()->getInvoiceAndShipmentSettings()->isCreateMagentoInvoice()) {
            return false;
        }

        $magentoOrder = $this->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createShipments(): ?array
    {
        if (!$this->canCreateShipments()) {
            if ($this->getMagentoOrder() && $this->getMagentoOrder()->getIsVirtual()) {
                $this->addInfoLog(
                    'Magento Order was created without the Shipping Address since your Virtual Product ' .
                    'has no weight and cannot be shipped.'
                );
            }

            return null;
        }

        $shipments = [];

        try {
            if (!$this->canCreateShipments()) {
                return null;
            }

            /** @var \M2E\TikTokShop\Model\Magento\Order\Shipment $shipmentBuilder */
            $shipmentBuilder = $this->shipmentFactory->create($this->getMagentoOrder());
            $shipmentBuilder->setMagentoOrder($this->getMagentoOrder());
            $shipmentBuilder->buildShipments();

            $shipments = $shipmentBuilder->getShipments();
        } catch (\Throwable $throwable) {
            $this->helperModuleException->process($throwable);
            $this->addErrorLog(
                'Shipment was not created. Reason: %msg%',
                ['msg' => $throwable->getMessage()]
            );
        }

        if ($shipments !== null) {
            foreach ($shipments as $shipment) {
                $this->addSuccessLog('Shipment #%shipment_id% was created.', [
                    '!shipment_id' => $shipment->getIncrementId(),
                ]);

                $this->addCreatedMagentoShipment($shipment);
            }

            /** @psalm-suppress TypeDoesNotContainType */
            if (empty($shipments)) {
                $this->addWarningLog('Shipment was not created.');
            }
        }

        return $shipments;
    }

    public function canCreateShipments(): bool
    {
        if (
            $this->isStatusPending()
            || $this->isStatusUnshipping()
        ) {
            return false;
        }

        if (!$this->getAccount()->getInvoiceAndShipmentSettings()->isCreateMagentoShipment()) {
            return false;
        }

        $magentoOrder = $this->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasShipments() || !$magentoOrder->canShip()) {
            return false;
        }

        $checkOrderItemsFn = static function (array $items) {
            /** @var \M2E\TikTokShop\Model\Order\Item $item */
            foreach ($items as $item) {
                if ($item->canCreateShipments()) {
                    return true;
                }
            }

            return false;
        };

        if (!$checkOrderItemsFn($this->getItems())) {
            return false;
        }

        return true;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function addCreatedMagentoShipment(\Magento\Sales\Model\Order\Shipment $magentoShipment): self
    {
        $additionalData = $this->getAdditionalData();
        $additionalData['created_shipments_ids'][] = $magentoShipment->getId();
        $this->setSettings('additional_data', $additionalData)->save();

        return $this;
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getBuyerUserId()
    {
        return $this->getData('buyer_user_id');
    }

    public function getBuyerMessage()
    {
        return $this->getData('buyer_message');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getPaidAmount()
    {
        return $this->getData('paid_amount');
    }

    public function getPaidAmountWithPlatformDiscount(): float
    {
        return $this->getPaidAmount() + $this->getPlatformDiscount();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getTaxRate(): float
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['rate'];
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getTaxDetails(): array
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getTaxAmount(): float
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)($taxDetails['amount'] ?? 0.0);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function isShippingPriceHasTax(): bool
    {
        if (!$this->hasShippingTax()) {
            return false;
        }

        if ($this->isVatTax()) {
            return true;
        }

        $taxDetails = $this->getTaxDetails();

        return isset($taxDetails['includes_shipping']) && $taxDetails['includes_shipping'];
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function hasShippingTax(): bool
    {
        return $this->getShippingTax() > 0;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getShippingTax()
    {
        $taxDetails = $this->getTaxDetails();

        return $taxDetails['shipping_fee_tax'] ?? 0.0;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function isVatTax(): bool
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();

        return $taxDetails['is_vat'];
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function hasTax(): bool
    {
        $taxDetails = $this->getTaxDetails();

        return !empty($taxDetails['rate']);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function isSalesTax(): bool
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();

        return !$taxDetails['is_vat'];
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getShippingService(): string
    {
        $shippingDetails = $this->getShippingDetails();

        return $shippingDetails['service'] ?? '';
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getShippingDate(): string
    {
        $shippingDetails = $this->getShippingDetails();

        return $shippingDetails['date'] ?? '';
    }

    public function getShippingDateTo()
    {
        return $this->getData('shipping_date_to');
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getShippingAddress(): \M2E\TikTokShop\Model\Order\ShippingAddress
    {
        if ($this->shippingAddress === null) {
            $shippingDetails = $this->getShippingDetails();
            $address = $shippingDetails['address'] ?? [];

            return $this
                ->shippingAddressFactory
                ->create($this)
                ->setData($address);
        }

        return $this->shippingAddress;
    }

    public function getPaymentMethod(): string
    {
        return $this->getData('payment_method_name') ?? '';
    }

    public function getPaymentDate(): string
    {
        return $this->getData('payment_date') ?? '';
    }

    public function getPurchaseUpdateDate()
    {
        return $this->getData('purchase_update_date');
    }

    public function getPurchaseCreateDate()
    {
        return $this->getData('purchase_create_date');
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getGrandTotalPrice(): ?float
    {
        if ($this->grandTotalPrice === null) {
            $this->grandTotalPrice = $this->getSubtotalPrice();
            $this->grandTotalPrice += round($this->getShippingPrice(), 2);
        }

        return $this->grandTotalPrice;
    }

    /**
     * @return float|int|null
     */
    public function getSubtotalPrice()
    {
        if ($this->subTotalPrice === null) {
            $subtotal = 0;

            foreach ($this->getItems() as $item) {
                $subtotal += $item->getSalePriceWithPlatformDiscount() * $item->getQtyPurchased();
            }

            $this->subTotalPrice = $subtotal;
        }

        return $this->subTotalPrice;
    }

    public function getShippingPrice(): float
    {
        $shippingPrice = $this->getPaymentDetails()['shipping_fee'] ?? 0.0;
        $shippingPrice += $this->getPaymentDetails()['shipping_fee_platform_discount'] ?? 0.0;

        return (float)$shippingPrice;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createTracks(): ?array
    {
        if (!$this->canCreateTracks()) {
            return null;
        }

        $shipmentTracks = [];

        try {
            $shipmentTracksBuilder = $this->magentoOrderShipmentTrackFactory
                ->create($this, $this->getShippingTrackingDetails());
            $shipmentTracks = $shipmentTracksBuilder->getTracks();
        } catch (\Throwable $throwable) {
            $this->addErrorLog(
                'Tracking details were not imported. Reason: %msg%',
                ['msg' => $throwable->getMessage()]
            );
        }

        if (!empty($shipmentTracks)) {
            $this->addSuccessLog('Tracking details were imported.');
        }

        return $shipmentTracks;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function canCreateTracks(): bool
    {
        $trackingDetails = $this->getShippingTrackingDetails();
        if (empty($trackingDetails)) {
            return false;
        }

        $magentoOrder = $this->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->hasShipments()) {
            return false;
        }

        return true;
    }

    public function getShippingTrackingDetails(): array
    {
        $trackingDetails = [];
        foreach ($this->getItems() as $orderItem) {
            $itemTrackingDetails = $orderItem->getTrackingDetails();
            if (empty($itemTrackingDetails)) {
                continue;
            }

            $trackNumber = $itemTrackingDetails['tracking_number'] ?? null;
            if (empty($trackNumber)) {
                continue;
            }

            if (array_key_exists($trackNumber, $trackingDetails)) {
                $trackingDetails[$trackNumber]['order_items'][] = $orderItem;
                continue;
            }

            $trackingDetails[$trackNumber] = $orderItem->getTrackingDetails();
            $trackingDetails[$trackNumber]['order_items'][] = $orderItem;
        }

        return array_values($trackingDetails);
    }

    public function canUpdatePaymentStatus(): bool
    {
        if ($this->isStatusPending()) {
            return false;
        }

        return true;
    }

    public function canUpdateShippingStatus(): bool
    {
        if (
            $this->isStatusPending()
            || $this->isStatusShipping()
            || $this->isStatusCanceled()
        ) {
            return false;
        }

        return true;
    }

    private function getPlatformDiscount(): float
    {
        return (float)($this->getPaymentDetails()['platform_discount'] ?? 0);
    }

    private function getPaymentDetails(): array
    {
        $details = $this->getData('payment_details');
        if (empty($details)) {
            return [];
        }

        return json_decode($details, true);
    }

    public function isBuyerCancellationRequest(): bool
    {
        return (bool)$this->getData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST);
    }

    public function buyerWantCancellation(string $reason)
    {
        $this->setData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST, 1);
        $this->setData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST_REASON, $reason);
    }

    public function buyerDontWantCancellation()
    {
        $this->setData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST, 0);
        $this->setData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST_REASON, null);
    }

    public function getBuyerCancellationRequestReason(): string
    {
        return (string)$this->getData(OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST_REASON);
    }

    public function getStatusForMagentoOrder(): string
    {
        if ($this->isStatusUnshipping()) {
            return $this->getAccount()->getOrdersSettings()->getStatusMappingForProcessing();
        }

        if ($this->isStatusShipping()) {
             return $this->getAccount()->getOrdersSettings()->getStatusMappingForProcessingShipped();
        }

        return '';
    }

    public function updateMagentoOrderStatus(): void
    {
        $magentoOrder = $this->getMagentoOrder();
        if ($magentoOrder === null) {
            return;
        }

        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);
        $magentoOrderUpdater->updateStatus($this->getStatusForMagentoOrder());

        $magentoOrderUpdater->finishUpdate();
    }

    public function markStatusUpdateRequired(): self
    {
        $this->statusUpdateRequired = true;

        return $this;
    }

    public function getStatusUpdateRequired(): bool
    {
        return $this->statusUpdateRequired;
    }
}
