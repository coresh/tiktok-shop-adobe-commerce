<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\Magento;

use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Listing\View\AbstractGrid
{
    protected \Magento\Store\Model\WebsiteFactory $websiteFactory;
    protected \Magento\Catalog\Model\Product\Attribute\Source\Status $status;
    protected \Magento\Catalog\Model\Product\Type $type;
    protected \Magento\Catalog\Model\Product\Visibility $visibility;
    protected \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    protected \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;
    private ListingProductResource $listingProductResource;

    public function __construct(
        ListingProductResource $listingProductResource,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        array $data = []
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->status = $status;
        $this->type = $type;
        $this->visibility = $visibility;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->magentoProductHelper = $magentoProductHelper;
        $this->listingProductResource = $listingProductResource;
        parent::__construct(
            $uiListingRuntimeStorage,
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionHelper,
            $data
        );
    }

    public function _construct(): void
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ttsListingViewGrid' . $this->getListing()->getId());
        // ---------------------------------------

        $this->hideMassactionColumn = true;
        $this->hideMassactionDropDown = true;
        $this->showAdvancedFilterProductsOption = false;
    }

    public function getGridUrl(): string
    {
        return $this->getUrl(
            '*/tiktokshop_listing/view',
            ['_current' => true]
        );
    }

    public function getRowUrl($item): bool
    {
        return false;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\TikTokShop\Model\Exception
     */
    protected function _prepareCollection(): Grid
    {
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->getSelect()->group('e.entity_id');
        $collection->setStoreId($this->getListing()->getStoreId());

        $collection
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->joinStockItem();

        $collection->joinTable(
            ['lp' => $this->listingProductResource->getMainTable()],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'available_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_category' => ListingProductResource::COLUMN_ONLINE_CATEGORY,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->getListing()->getId()
            )
        );

        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'magento_price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail',
                'catalog_product/thumbnail',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }

        $this->setCollection($collection);

        $this->getCollection()->addWebsiteNamesToResult();

        return parent::_prepareCollection();
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns(): Grid
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('ID'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'number',
                'index' => 'entity_id',
                'filter_index' => 'entity_id',
                'store_id' => $this->getListing()->getStoreId(),
                'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Title'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'name',
                'filter_index' => 'name',
                'escape' => false,
                'frame_callback' => [$this, 'callbackColumnProductTitle'],
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'align' => 'left',
                'width' => '90px',
                'type' => 'options',
                'sortable' => false,
                'index' => 'type_id',
                'filter_index' => 'type_id',
                'options' => $this->getProductTypes(),
            ]
        );

        $this->addColumn(
            'is_in_stock',
            [
                'header' => __('Stock Availability'),
                'align' => 'left',
                'width' => '90px',
                'type' => 'options',
                'sortable' => false,
                'index' => 'is_in_stock',
                'filter_index' => 'is_in_stock',
                'options' => [
                    '1' => __('In Stock'),
                    '0' => __('Out of Stock'),
                ],
                'frame_callback' => [$this, 'callbackColumnIsInStock'],
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'align' => 'left',
                'width' => '90px',
                'type' => 'text',
                'index' => 'sku',
                'filter_index' => 'sku',
            ]
        );

        $store = $this->_getStore();

        $priceAttributeAlias = 'price';
        if ($store->getId()) {
            $priceAttributeAlias = 'magento_price';
        }

        $this->addColumn(
            $priceAttributeAlias,
            [
                'header' => __('Price'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'price',
                'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Price::class,
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => $priceAttributeAlias,
                'filter_index' => $priceAttributeAlias,
                'frame_callback' => [$this, 'callbackColumnPrice'],
            ]
        );

        $this->addColumn(
            'qty',
            [
                'header' => __('QTY'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'number',
                'index' => 'qty',
                'filter_index' => 'qty',
                'frame_callback' => [$this, 'callbackColumnQty'],
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'align' => 'left',
                'width' => '90px',
                'type' => 'options',
                'sortable' => false,
                'index' => 'visibility',
                'filter_index' => 'visibility',
                'options' => $this->visibility->getOptionArray(),
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'width' => '90px',
                'type' => 'options',
                'sortable' => false,
                'index' => 'status',
                'filter_index' => 'status',
                'options' => $this->status->getOptionArray(),
                'frame_callback' => [$this, 'callbackColumnStatus'],
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                [
                    'header' => __('Websites'),
                    'align' => 'left',
                    'width' => '90px',
                    'type' => 'options',
                    'sortable' => false,
                    'index' => 'websites',
                    'filter_index' => 'websites',
                    'options' => $this->websiteFactory->create()->getCollection()->toOptionHash(),
                ]
            );
        }

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $rowVal = $row->getData();

        if (
            $column->getId() == 'magento_price' &&
            (!isset($rowVal['magento_price']) || (float)$rowVal['magento_price'] <= 0)
        ) {
            $value = '<span style="color: red;">0</span>';
        }

        if (
            $column->getId() == 'price' &&
            (!isset($rowVal['price']) || (float)$rowVal['price'] <= 0)
        ) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    //########################################

    private function _getStore(): \Magento\Store\Api\Data\StoreInterface
    {
        $storeId = $this->getListing()->getStoreId();

        return $this->_storeManager->getStore($storeId);
    }

    // ----------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function getProductTypes(): array
    {
        $magentoProductTypes = $this->type->getOptionArray();
        $knownTypes = $this->magentoProductHelper->getOriginKnownTypes();

        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (in_array($type, $knownTypes)) {
                continue;
            }

            unset($magentoProductTypes[$type]);
        }

        return $magentoProductTypes;
    }
}
