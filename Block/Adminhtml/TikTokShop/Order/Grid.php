<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid;
use M2E\TikTokShop\Model\ResourceModel\Order\Item as OrderItemResource;

class Grid extends AbstractGrid
{
    private \M2E\TikTokShop\Model\ResourceModel\Order\Note\Collection $notesCollection;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Item\Collection $itemsCollection;
    private \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \Magento\Sales\Model\ResourceModel\Order $magentoOrderResource;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\StatusHelper $orderStatusHelper;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\TikTokShop\Model\Currency $currency;
    private \M2E\TikTokShop\Model\Order\Log\ServiceFactory $orderLogServiceFactory;
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Model\Order\Log\ServiceFactory $orderLogServiceFactory,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\StatusHelper $orderStatusHelper,
        \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order $magentoOrderResource,
        \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Model\Currency $currency,
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->magentoOrderResource = $magentoOrderResource;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->urlHelper = $urlHelper;
        $this->currency = $currency;
        $this->orderLogServiceFactory = $orderLogServiceFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->orderItemRepository = $orderItemRepository;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('tikTokOrderGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('purchase_create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderCollectionFactory->create();

        $collection
            ->getSelect()
            ->joinLeft(
                ['so' => $this->magentoOrderResource->getMainTable()],
                '(so.entity_id = `main_table`.magento_order_id)',
                ['magento_order_num' => 'increment_id']
            );

        // Add Filter By Account
        // ---------------------------------------
        if ($accountId = $this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }
        // ---------------------------------------

        // Add Filter By Shop
        // ---------------------------------------
        if ($shopId = $this->getRequest()->getParam('shop')) {
            $collection->addFieldToFilter('main_table.shop_id', $shopId);
        }
        // ---------------------------------------

        // Add Not Created Magento Orders Filter
        // ---------------------------------------
        if ($this->getRequest()->getParam('not_created_only')) {
            $collection->addFieldToFilter('magento_order_id', ['null' => true]);
        }
        // ---------------------------------------

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $orderIds = $this->getCollection()->getColumnValues('id');

        $this->itemsCollection = $this->orderItemCollectionFactory->create();
        $this->itemsCollection->addFieldToFilter('order_id', ['in' => $orderIds]);

        $this->notesCollection = $this->orderNoteCollectionFactory->create();
        $this->notesCollection->addFieldToFilter('order_id', ['in' => $orderIds]);

        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'purchase_create_date',
            [
                'header' => __('Sale Date'),
                'align' => 'left',
                'type' => 'datetime',
                'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
                'format' => \IntlDateFormatter::MEDIUM,
                'filter_time' => true,
                'index' => 'purchase_create_date',
                'width' => '170px',
                'frame_callback' => [$this, 'callbackPurchaseCreateDate'],
            ]
        );

        $this->addColumn(
            'deliver_by_date',
            [
                'header' => __('Deliver By Date'),
                'align' => 'left',
                'type' => 'datetime',
                'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
                'format' => \IntlDateFormatter::MEDIUM,
                'filter_time' => true,
                'index' => 'deliver_by_date',
                'width' => '170px',
                'frame_callback' => [$this, 'callbackDeliverByDate'],
            ]
        );

        $this->addColumn(
            'ship_by_date',
            [
                'header' => __('Ship By Date'),
                'align' => 'left',
                'type' => 'datetime',
                'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
                'format' => \IntlDateFormatter::MEDIUM,
                'filter_time' => true,
                'index' => 'ship_by_date',
                'width' => '170px',
                'frame_callback' => [$this, 'callbackShipByDate'],
            ]
        );

        $this->addColumn(
            'magento_order_num',
            [
                'header' => __('Magento Order #'),
                'align' => 'left',
                'index' => 'magento_order_num',
                'filter_index' => 'so.increment_id',
                'width' => '200px',
                'frame_callback' => [$this, 'callbackColumnMagentoOrder'],
            ]
        );

        $this->addColumn(
            'tts_order_id',
            [
                'header' => __('TikTokShop Order #'),
                'align' => 'left',
                'width' => '145px',
                'index' => 'tts_order_id',
                'frame_callback' => [$this, 'callbackColumnTitTokShopOrder'],
                'filter' => \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Grid\Column\Filter\OrderId::class,
                'filter_condition_callback' => [$this, 'callbackFilterTTSOrderId'],
            ]
        );

        $this->addColumn(
            'order_items',
            [
                'header' => __('Items'),
                'align' => 'left',
                'index' => 'order_items',
                'sortable' => false,
                'width' => '*',
                'frame_callback' => [$this, 'callbackColumnItems'],
                'filter_condition_callback' => [$this, 'callbackFilterItems'],
            ]
        );

        $this->addColumn(
            'buyer',
            [
                'header' => __('Buyer'),
                'align' => 'left',
                'index' => 'buyer_user_id',
                'frame_callback' => [$this, 'callbackColumnBuyer'],
                'filter_condition_callback' => [$this, 'callbackFilterBuyer'],
                'width' => '120px',
            ]
        );

        $this->addColumn(
            'paid_amount',
            [
                'header' => __('Total Paid'),
                'align' => 'left',
                'width' => '110px',
                'index' => 'paid_amount',
                'type' => 'number',
                'frame_callback' => [$this, 'callbackColumnTotal'],
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'status',
                'type' => 'options',
                'options' => $this->orderStatusHelper->getStatusesOptions(),
                'frame_callback' => [$this, 'callbackColumnStatus'],
                'filter_condition_callback' => [$this, 'callbackFilterStatus'],
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $groups = [
            'general' => __('General'),
            'order_cancellation' => __('Order Cancellation'),
        ];

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'reservation_place',
            [
                'label' => __('Reserve QTY'),
                'url' => $this->getUrl('*/order/reservationPlace'),
                'confirm' => __('Are you sure?'),
            ],
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'reservation_cancel',
            [
                'label' => __('Cancel QTY Reserve'),
                'url' => $this->getUrl('*/order/reservationCancel'),
                'confirm' => __('Are you sure?'),
            ],
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'resend_shipping',
            [
                'label' => __('Resend Shipping Information'),
                'url' => $this->getUrl('*/order/resubmitShippingInfo'),
                'confirm' => __('Are you sure?'),
            ],
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'create_order',
            [
                'label' => __('Create Magento Order'),
                'url' => $this->getUrl('*/tiktokshop_order/CreateMagentoOrder'),
                'confirm' => __('Are you sure?'),
            ],
            'general'
        );

        return parent::_prepareMassaction();
    }

    public function callbackColumnMagentoOrder($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row->getData('magento_order_id');
        $returnString = __('N/A');

        if ($magentoOrderId !== null) {
            if (!empty($value)) {
                $magentoOrderNumber = \M2E\Core\Helper\Data::escapeHtml($value);
                $orderUrl = $this->getUrl('sales/order/view', ['order_id' => $magentoOrderId]);
                $returnString = '<a href="' . $orderUrl . '" target="_blank">' . $magentoOrderNumber . '</a>';
            } else {
                $returnString = '<span style="color: red;">' . __('Deleted') . '</span>';
            }
        }

        /** @var \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Order $viewLogIcon */
        $viewLogIcon = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Order::class);
        $logIconHtml = $viewLogIcon->render($row);

        if ($logIconHtml !== '') {
            return '<div style="min-width: 100px">' . $returnString . $logIconHtml . '</div>';
        }

        return $returnString;
    }

    public function callbackPurchaseCreateDate($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        $purchaseDate = $row->getPurchaseCreateDate();
        if (empty($purchaseDate)) {
            return '';
        }

        return $this->_localeDate->formatDate(
            $purchaseDate,
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    public function callbackShipByDate($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        $shippingDate = $row->getShipByDate();
        if (empty($shippingDate)) {
            return '';
        }

        return $this->_localeDate->formatDate(
            $shippingDate,
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    public function callbackDeliverByDate($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        $deliveringDate = $row->getDeliverByDate();
        if (empty($deliveringDate)) {
            return '';
        }

        return $this->_localeDate->formatDate(
            $deliveringDate,
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    public function callbackColumnTitTokShopOrder($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        $back = $this->urlHelper->makeBackUrlParam('*/tiktokshop_order/index');
        $itemUrl = $this->getUrl('*/tiktokshop_order/view', ['id' => $row->getId(), 'back' => $back]);

        $returnString = '';

        if ($row->hasOrderReturnRequestedItemInPendingStatus()) {
            $text = __('Return requested');
            $returnString .= sprintf('<br/><span style="color: red;">%s</span><br/>', $text);
        }

        $returnString .= sprintf('<a href="%s">%s</a>', $itemUrl, $row->getTtsOrderId());

        /** @var \M2E\TikTokShop\Model\Order\Note[] $notes */
        $notes = $this->notesCollection->getItemsByColumnValue('order_id', $row->getId());
        $returnString .= $this->formatNotes($notes);

        return $returnString;
    }

    /**
     * @param string $text
     * @param int $maxLength
     *
     * @return string
     */
    private function cutText(string $text, int $maxLength): string
    {
        return mb_strlen($text) > $maxLength ? mb_substr($text, 0, $maxLength) . "..." : $text;
    }

    /**
     * @param $notes
     *
     * @return string
     */
    private function formatNotes($notes)
    {
        $notesHtml = '';
        $maxLength = 250;

        if (!$notes) {
            return '';
        }

        $notesHtml .= <<<HTML
    <div class="note_icon admin__field-tooltip">
        <a class="admin__field-tooltip-note-action" href="javascript://"></a>
        <div class="admin__field-tooltip-content" style="right: -4.4rem">
            <div class="tts-identifiers">
HTML;

        if (count($notes) === 1) {
            $noteValue = $notes[0]->getNote();
            $shortenedNote = $this->cutText($noteValue, $maxLength);
            $notesHtml .= "<div>{$shortenedNote}</div>";
        } else {
            $notesHtml .= "<ul>";
            foreach ($notes as $note) {
                $noteValue = $note->getNote();
                $shortenedNote = $this->cutText($noteValue, $maxLength);
                $notesHtml .= "<li>{$shortenedNote}</li>";
            }
            $notesHtml .= "</ul>";
        }

        $notesHtml .= <<<HTML
            </div>
        </div>
    </div>
HTML;

        return $notesHtml;
    }

    public function callbackColumnItems($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        $itemsCollection = $this->orderItemRepository->getGroupOrderItemCollection((int)$row->getId());
        $items = $itemsCollection->getItems();

        $itemLines = [];
        foreach ($items as $item) {
            try {
                $product = $item->getProduct();
            } catch (\M2E\Core\Model\Exception $e) {
                $product = null;
                $logService = $this->orderLogServiceFactory->create();

                $logService->addMessage(
                    $row->getId(),
                    $e->getMessage(),
                    \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR
                );
            }

            $skuHtml = '';
            if ($item->getSku()) {
                $sku = \M2E\Core\Helper\Data::escapeHtml($item->getSku());
                if ($product !== null) {
                    $sku = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $this->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                        $sku
                    );
                }

                $skuHtml = sprintf(
                    '<span style="padding-left: 10px;"><b>%s:</b>&nbsp;%s</span><br/>',
                    __('SKU'),
                    $sku
                );
            }

            $qtyPurchasedHtml = sprintf(
                '<span style="padding-left: 10px;"><b>%s:</b>&nbsp;%d</span><br/>',
                __('QTY'),
                $item->getData(\M2E\TikTokShop\Model\Order\Item\Repository::COLUMN_GROUPED_QTY)
            );

            $itemLines[] = sprintf(
                '%s<br><small>%s%s</small>',
                \M2E\Core\Helper\Data::escapeHtml($item->getChannelProductTitle()),
                $skuHtml,
                $qtyPurchasedHtml
            );
        }

        $html = '';
        if ($row->isBuyerCancellationRequest()) {
            $html .= sprintf('<p style="color: #ff0000">%s</p>', __('Cancellation Requested'));
        }

        return $html . implode('<br>', $itemLines);
    }

    public function callbackColumnBuyer($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        $returnString = \M2E\Core\Helper\Data::escapeHtml($row->getBuyerName()) . '<br/>';
        $returnString .= \M2E\Core\Helper\Data::escapeHtml($row->getBuyerUserId());

        return $returnString;
    }

    public function callbackColumnTotal($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport)
    {
        if ($row->isSample()) {
            return 0;
        }

        return $this->currency->formatPrice($row->getCurrency(), $row->getPaidAmountWithPlatformDiscount());
    }

    public function callbackColumnStatus($value, \M2E\TikTokShop\Model\Order $row, $column, $isExport): string
    {
        $status = $row->getOrderStatus();

        return sprintf(
            '<span style="color: %s">%s</span>',
            $this->orderStatusHelper->getStatusColor($status),
            $this->orderStatusHelper->getStatusLabel($status),
        );
    }

    protected function callbackFilterTTSOrderId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (empty($value)) {
            return;
        }

        if (!empty($value['value'])) {
            $collection->getSelect()->where('tts_order_id LIKE ?', "%{$value['value']}%");
        }
    }

    protected function callbackFilterItems($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $orderItemsCollection = $this->orderItemCollectionFactory->create();

        $orderItemsCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $orderItemsCollection->getSelect()->columns(OrderItemResource::COLUMN_ORDER_ID);
        $orderItemsCollection->getSelect()->distinct();

        $orderItemsCollection
            ->getSelect()
            ->where(
                sprintf(
                    '%s LIKE ? OR %s LIKE ? OR %s LIKE ?',
                    OrderItemResource::COLUMN_TTS_ITEM_ID,
                    OrderItemResource::COLUMN_TITLE,
                    OrderItemResource::COLUMN_SKU
                ),
                "%$value%"
            );

        $ordersIds = $orderItemsCollection->getColumnValues(OrderItemResource::COLUMN_ORDER_ID);
        $collection->addFieldToFilter('main_table.id', ['in' => $ordersIds]);
    }

    protected function callbackFilterBuyer($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection
            ->getSelect()
            ->where('buyer_email LIKE ? OR buyer_user_id LIKE ? OR buyer_name LIKE ?', '%' . $value . '%');
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter('order_status', ['eq' => $value]);
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/tiktokshop_order/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
                OrderObj.initializeGrids();
JS
            );

            return parent::_toHtml();
        }

        $tempGridIds = [];
        $tempGridIds[] = $this->getId();
        $tempGridIds = \M2E\Core\Helper\Json::encode($tempGridIds);

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\Log\AbstractModel::class)
        );

        $this->jsTranslator->add('View Full Order Log', __('View Full Order Log'));

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/Order'
    ], function(){
        window.OrderObj = new Order('$tempGridIds');
        OrderObj.initializeGrids();
    });
JS
        );

        return parent::_toHtml();
    }
}
