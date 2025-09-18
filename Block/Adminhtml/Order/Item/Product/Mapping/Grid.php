<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Mapping;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid;

class Grid extends AbstractGrid
{
    protected int $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

    private \Magento\Catalog\Model\Product\Type $productTypeModel;
    private \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory;
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \Magento\Catalog\Model\Product\Type $productTypeModel,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->productTypeModel = $productTypeModel;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('orderItemProductMappingGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        /** @var \M2E\TikTokShop\Model\Order\Item $orderItem */
        $itemId = $this->getRequest()->getParam('order_item_id');

        $orderItem = $this->orderItemRepository->find((int)$itemId);
        if ($orderItem !== null) {
            $this->storeId = $orderItem->getStoreId();
        }

        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setStoreId($this->storeId);

        $collection->addFieldToFilter(
            [
                [
                    'attribute' => 'type_id',
                    'in' => \M2E\TikTokShop\Model\Order::SUPPORTED_MAGENTO_PRODUCT_TYPES,
                ],
            ]
        );

        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('type_id')
            ->joinStockItem();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'type' => 'number',
            'width' => '60px',
            'index' => 'entity_id',
            'filter_index' => 'entity_id',
            'store_id' => $this->storeId,
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('title', [
            'header' => __('Product Title / Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'width' => '350px',
            'index' => 'name',
            'filter_index' => 'name',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('type_id', [
            'header' => __('Type'),
            'width' => '60px',
            'index' => 'type_id',
            'sortable' => false,
            'type' => 'options',
            'options' => $this->getProductTypes(),
        ]);

        $this->addColumn('stock_availability', [
            'header' => __('Stock Availability'),
            'width' => '100px',
            'index' => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                1 => __('In Stock'),
                0 => __('Out of Stock'),
            ],
            'frame_callback' => [$this, 'callbackColumnIsInStock'],
        ]);

        $this->addColumn('actions', [
            'header' => __('Actions'),
            'align' => 'left',
            'type' => 'text',
            'width' => '125px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnActions'],
        ]);
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px">' . \M2E\Core\Helper\Data::escapeHtml($value);

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = $this->ourMagentoProductFactory
                ->createByProductId((int)$row->getData('entity_id'))
                ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') . ':</strong> ';
        $value .= \M2E\Core\Helper\Data::escapeHtml($sku) . '</div>';

        return $value;
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        return '<div style="margin-left: 3px">' . \M2E\Core\Helper\Data::escapeHtml($value) . '</div>';
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ($row->getData('is_in_stock') === null) {
            return __('N/A');
        }

        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">' . __('Out of Stock') . '</span>';
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $productId = (int)$row->getId();

        if (empty($productId)) {
            return 'Not Found Product ID';
        }

        return sprintf(
            '<a href="javascript:void(0);" onclick="%s">%s</a>',
            sprintf("OrderEditItemObj.assignProduct('%s');", $productId),
            __('Link To This Product')
        );
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
            ]
        );
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/order/productMappingGrid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    //########################################

    private function getProductTypes(): array
    {
        $magentoProductTypes = $this->productTypeModel->getOptionArray();

        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (in_array($type, \M2E\TikTokShop\Model\Order::SUPPORTED_MAGENTO_PRODUCT_TYPES)) {
                continue;
            }

            unset($magentoProductTypes[$type]);
        }

        return $magentoProductTypes;
    }
}
