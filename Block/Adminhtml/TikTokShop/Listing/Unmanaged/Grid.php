<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged;

use M2E\TikTokShop\Model\Product;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Shop $shopResource;
    private \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku $variantSkuResource;
    private \M2E\TikTokShop\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage,
        \M2E\TikTokShop\Model\ResourceModel\Shop $shopResource,
        \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku $variantSkuResource,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->uiAccountRuntimeStorage = $uiAccountRuntimeStorage;
        $this->localeCurrency = $localeCurrency;
        $this->resourceConnection = $resourceConnection;
        $this->unmanagedRepository = $unmanagedRepository;
        $this->shopResource = $shopResource;
        $this->variantSkuResource = $variantSkuResource;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ttsListingUnmanagedGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/tiktokshop_listing_unmanaged/index',
            ['_current' => true, 'account' => $this->uiAccountRuntimeStorage->getAccount()->getId()]
        );
    }

    protected function _prepareCollection()
    {
        $collection = $this->unmanagedRepository->createCollection();

        $collection->join(
            ['shop' => $this->shopResource->getMainTable()],
            sprintf(
                'main_table.%s = shop.%s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_SHOP_ID,
                \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_ID,
            ),
            [
                'region' => \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_REGION,
            ]
        );

        // Left join with variant_sku table to get the first child's sku_id
        $collection->getSelect()->joinLeft(
            ['variant_sku' => $this->variantSkuResource->getMainTable()],
            sprintf(
                'main_table.id = variant_sku.product_id AND variant_sku.id = (
                SELECT MIN(vs.id)
                FROM %s vs
                WHERE vs.product_id = main_table.id
            )',
                $this->variantSkuResource->getMainTable()
            ),
            [
                'sku_id' => 'sku_id',
                'price' => 'variant_sku.price',
            ]
        );

        $collection->addFieldToFilter(
            sprintf('main_table.%s', \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_ACCOUNT_ID),
            ['eq' => $this->uiAccountRuntimeStorage->getAccount()->getId()]
        );

        if ($shopId = $this->getRequest()->getParam('shop')) {
            $collection->addFieldToFilter(
                sprintf('main_table.%s', \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_SHOP_ID),
                ['eq' => (int)$shopId]
            );
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged\Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvUnmanagedGrid', __('CSV'));

        $this->addColumn('magento_product_id', [
            'header' => __('Product ID'),
            'align' => 'left',
            'type' => 'number',
            'width' => '80px',
            'sortable' => true,
            'index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID,
            'filter_index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID,
            'frame_callback' => [$this, 'callbackColumnProductId'],
            'filter' => \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Filter\ProductId::class,
            'filter_condition_callback' => [$this, 'callbackFilterProductId'],
        ]);

        $this->addColumn('title', [
            'header' => __('Product Title / Product SKU / TikTokShop Category'),
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'sortable' => true,
            'index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_TITLE,
            'escape' => false,
            'filter_index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_TITLE,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('product_id', [
            'header' => __('TikTok Shop Product ID'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'sortable' => true,
            'index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_TTS_PRODUCT_ID,
            'filter_index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_TTS_PRODUCT_ID,
            'frame_callback' => [$this, 'callbackColumnTTSProductId'],
        ]);

        $this->addColumn('sku_id', [
            'header' => __('SKU ID'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'sortable' => true,
            'index' => 'variant_sku.sku_id',
            'frame_callback' => [$this, 'callbackColumnSkuId'],
            'filter_condition_callback' => [$this, 'callbackFilterSkuId']
        ]);

        $this->addColumn('online_qty', [
            'header' => __('Available QTY'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'sortable' => true,
            'index' => \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_QTY,
            'frame_callback' => [$this, 'callbackColumnQty'],
            'filter_condition_callback' => [$this, 'callbackFilterQty'],
        ]);

        $this->addColumn('online_price', [
            'header' => __('Price'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'sortable' => true,
            'index' => 'variant_sku.price',
            'frame_callback' => [$this, 'callbackColumnOnlinePrice'],
            'filter_condition_callback' => [$this, 'callbackFilterPrice'],

        ]);

        $this->addColumn('status', [
            'header' => __('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
                Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
            ],
            'frame_callback' => [$this, 'callbackColumnStatus'],
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups([
            'mapping' => __('Linking'),
            'other' => __('Other'),
        ]);

        $this->getMassactionBlock()->addItem('autoMapping', [
            'label' => __('Link Item(s) Automatically'),
            'url' => '',
        ], 'mapping');

        $this->getMassactionBlock()->addItem('moving', [
            'label' => __('Move Item(s) to Listing'),
            'url' => '',
        ], 'other');
        $this->getMassactionBlock()->addItem('removing', [
            'label' => __('Remove Item(s)'),
            'url' => '',
        ], 'other');
        $this->getMassactionBlock()->addItem('unmapping', [
            'label' => __('Unlink Item(s)'),
            'url' => '',
        ], 'mapping');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/other/view/grid.css');

        return parent::_prepareLayout();
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return string
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            if ($isExport) {
                return '';
            }

            $productTitle = $row->getTitle();
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }
            $productTitle = \M2E\TikTokShop\Helper\Data::escapeHtml($productTitle);
            $productTitle = \M2E\TikTokShop\Helper\Data::escapeJs($productTitle);

            return sprintf(
                '<a onclick="ListingOtherMappingObj.openPopUp(%s, \'%s\')">%s</a>',
                (int)$row->getId(),
                $productTitle,
                __('Link')
            );
        }

        if ($isExport) {
            return $row->getMagentoProductId();
        }

        $viewProductUrl = $this->getUrl(
            'catalog/product/edit',
            ['id' => $row->getMagentoProductId()]
        );

        $editLink = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $viewProductUrl,
            $row->getMagentoProductId()
        );

        $moveLink = sprintf(
            '<a onclick="TiktokshopListingOtherGridObj.movingHandler.getGridHtml(%s)">%s</a>',
            \M2E\TikTokShop\Helper\Json::encode([(int)$row->getId()]),
            __('Move')
        );

        return $editLink . ' &nbsp;&nbsp;&nbsp;' . $moveLink;
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $title = $row->getTitle();
        $tempSku = \M2E\TikTokShop\Helper\Data::escapeHtml(!$row->isSimple() ? '' : $row->getSku());

        if ($isExport) {
            return strip_tags($tempSku);
        }

        $categoryHtml = '';
        if ($row->getCategoriesData() !== []) {
            $parts = array_map(function (array $category) {
                $categoryName = $category['local_name'];
                if ($category['is_leaf']) {
                    return sprintf('%s (%s)', $categoryName, $category['id']);
                }
                return $categoryName;
            }, $row->getCategoriesData());
            $category = implode(' >> ', $parts);
            $categoryHtml = sprintf(
                '<strong>%s:</strong>&nbsp;%s',
                __('Category'),
                \M2E\TikTokShop\Helper\Data::escapeHtml($category)
            );
        }

        $additionalInfo = $this->getProductTitleAdditionalInfo(
            $row->getAccount()->getTitle(),
            $row->getShop()->getShopName(),
            $this->getRequest()->getParam('account') === null,
            $this->getRequest()->getParam('shop') === null
        ) ?? '';

        $skuHtml = '';
        if (!empty($tempSku)) {
            $titleSku = __('SKU');
            $skuHtml = sprintf('<strong>%s:&nbsp;</strong>%s<br/>', $titleSku, $tempSku);
        }

        $salesAttributes = '';
        if (!$row->isSimple()) {
            $salesAttributes = $this->renderVariantLine($row);
        }

        return sprintf(
            '<span>%s</span><br/>%s%s%s%s',
            \M2E\TikTokShop\Helper\Data::escapeHtml($title),
            $skuHtml,
            $categoryHtml,
            $additionalInfo,
            $salesAttributes
        );
    }

    private function renderVariantLine(\M2E\TikTokShop\Model\UnmanagedProduct $unmanagedProduct): string
    {
        $salesAttributes = $unmanagedProduct->getSalesAttributeNames();
        $configurableAttributes = array_map(
            static function (string $attributeName) {
                return sprintf('<span>%s</span>', $attributeName);
            },
            $salesAttributes
        );

        return sprintf(
            '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">%s</div>',
            implode(', ', $configurableAttributes)
        );
    }

    private function getProductTitleAdditionalInfo(
        string $accountTitle,
        string $shopTitle,
        bool $accountUnfiltered,
        bool $shopUnfiltered
    ): ?string {
        if ($accountUnfiltered && $shopUnfiltered) {
            return sprintf(
                '<br/><strong>%s:</strong> %s, <br/><strong>%s:</strong> %s',
                __('Account'),
                $accountTitle,
                __('Shop'),
                $shopTitle
            );
        }

        if ($accountUnfiltered) {
            return sprintf('<br/><strong>%s:</strong> %s', __('Account'), $accountTitle);
        }

        if ($shopUnfiltered) {
            return sprintf('<br/><strong>%s:</strong> %s', __('Shop'), $shopTitle);
        }

        return null;
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function callbackColumnItemId($value, $row, $column, $isExport)
    {
        $value = $row->getProductId();

        if ($isExport) {
            return $value;
        }

        if (empty($value)) {
            return __('N/A');
        }

        return $value;
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return int|\Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    public function callbackColumnOnlinePrice($value, $row, $column, $isExport)
    {
        $currencyCode = $row->getCurrency();

        if ($row->isSimple()) {
            $value = $row->getPrice();
            if (empty($value)) {
                if ($isExport) {
                    return '';
                }

                return __('N/A');
            }

            if ($value <= 0) {
                if ($isExport) {
                    return 0;
                }

                return '<span style="color: #f00;">0</span>';
            }

            return $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($value);
        }

        $onlineMinPrice = $row->getMinPrice();
        $onlineMaxPrice = $row->getMaxPrice();

        if (empty($onlineMinPrice) && empty($onlineMaxPrice)) {
            if ($isExport) {
                return '';
            }

            return (string)__('N/A');
        }

        if (
            (!empty($onlineMinPrice) && empty($onlineMaxPrice))
            || $onlineMinPrice === $onlineMaxPrice
        ) {
            return $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($onlineMinPrice);
        }

        if (
            $onlineMaxPrice !== null
            && $onlineMinPrice === null
        ) {
            return $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($onlineMaxPrice);
        }

        $formattedMinPrice = $this->localeCurrency
            ->getCurrency($currencyCode)
            ->toCurrency($onlineMinPrice);

        $formattedMaxPrice = $this->localeCurrency
            ->getCurrency($currencyCode)
            ->toCurrency($onlineMaxPrice);

        return sprintf('%s - %s', $formattedMinPrice, $formattedMaxPrice);
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Collection $collection
     * @param $column
     */
    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= sprintf(
                '(%s >= %s)',
                'price',
                $collection->getConnection()->quote($value['from'])
            );
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (!empty($where)) {
                $where .= ' AND ';
            }

            $where .= sprintf(
                '(%s <= %s)',
                'price',
                $collection->getConnection()->quote($value['to'])
            );
        }

        if (isset($value['from']) && $value['from'] != '') {
            $where .= sprintf(
                ' AND (%s >= %s)',
                'variant_sku.price',
                $collection->getConnection()->quote($value['from'])
            );
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (!empty($where)) {
                $where .= ' AND ';
            }

            $where .= sprintf(
                '(%s <= %s)',
                'variant_sku.price',
                $collection->getConnection()->quote($value['to'])
            );
        }

        if (!empty($where)) {
            $collection->getSelect()->where($where);
        }
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return int|\Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    public function callbackColumnSkuId($value, $row, $column, $isExport)
    {
        $skuId = $row->isSimple() ? $row->getSkuId() : '';

        if ($isExport) {
            return $skuId;
        }

        return sprintf('%s', $skuId);
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Collection $collection
     * @param $column
     */
    public function callbackFilterSkuId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $conditions = [
            sprintf(
                '(main_table.%s = 1 AND variant_sku.sku_id LIKE ?)',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_IS_SIMPLE
            )
        ];

        $collection->getSelect()->where(
            implode($conditions),
            ['%' . $value . '%']
        );
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return int|\Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        $qty = $row->getQty() ?? '';

        if ($isExport) {
            return $qty;
        }

        return sprintf('%s', $qty);
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Collection $collection
     * @param $column
     */
    public function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= sprintf(
                'main_table.%s >= %s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_QTY,
                $collection->getConnection()->quote($value['from'])
            );
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= sprintf(
                'main_table.%s <= %s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_QTY,
                $collection->getConnection()->quote($value['to'])
            );
        }

        $collection->getSelect()->where($where);
    }

    /**
     * @param $value
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        $coloredStatuses = [
            Product::STATUS_LISTED => 'green',
            Product::STATUS_INACTIVE => 'red',
            Product::STATUS_BLOCKED => 'orange',
        ];

        $status = $row->getStatus();

        if (isset($coloredStatuses[$status])) {
            $value = sprintf(
                '<span style="color: %s">%s</span>',
                $coloredStatuses[$status],
                $value
            );
        }

        return $value . $this->getLockedTag($row);
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Collection $collection
     * @param $column
     *
     * @return void
     */
    protected function callbackFilterProductId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= sprintf(
                'main_table.%s >= %s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID,
                $collection->getConnection()->quote($value['from'])
            );
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= sprintf(
                'main_table.%s <= %s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID,
                $collection->getConnection()->quote($value['to'])
            );
        }

        if (isset($value['is_mapped']) && $value['is_mapped'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ($value['is_mapped']) {
                $where .= sprintf(
                    'main_table.%s IS NOT NULL',
                    \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID
                );
            } else {
                $where .= sprintf(
                    'main_table.%s IS NULL',
                    \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID
                );
            }
        }

        $collection->getSelect()->where($where);
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Collection $collection
     * @param $column
     */
    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $condition = sprintf(
            '%s LIKE ? OR %s LIKE ?',
            \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_TITLE,
            \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_CATEGORIES_DATA
        );
        $collection->getSelect()->where($condition, "%$value%");
    }

    private function getLockedTag(\M2E\TikTokShop\Model\UnmanagedProduct $listingOther): string
    {
        $html = '';
        $processingLocks = [];
        foreach ($processingLocks as $processingLock) {
            switch ($processingLock->getTag()) {
                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

    protected function _beforeToHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('isAjax')) {
            $this->js->addRequireJs(
                [
                    'jQuery' => 'jquery',
                ],
                <<<JS

            TiktokshopListingOtherGridObj.afterInitPage();
JS
            );
        }

        return parent::_beforeToHtml();
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function callbackColumnTTSProductId($value, $row, $column, $isExport)
    {
        $ttsProductId = $row->getData(\M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_TTS_PRODUCT_ID);

        if (empty($ttsProductId)) {
            return (string)__('N/A');
        }

        $region = $row->getData('region') ?? null;

        $url = \M2E\TikTokShop\Model\Product::getProductLinkOnChannel($ttsProductId, $region);

        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $ttsProductId);
    }
}
