<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\TikTokShop;

use M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\TtsProductId as TtsProductIdRender;
use M2E\TikTokShop\Block\Adminhtml\Log\AbstractGrid;
use M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\TikTokShop\Row as Row;
use M2E\TikTokShop\Model\Product;
use M2E\TikTokShop\Model\Product\ListingQuality as ProductListingQuality;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;
use M2E\TikTokShop\Model\ResourceModel\Promotion as PromotionResource;
use M2E\TikTokShop\Model\ResourceModel\Promotion\Product as PromotionProductResource;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Listing\View\AbstractGrid
{
    private const COLUMN_INDEX_VARIANTS_PRICE = 'variants_price';

    private \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary $categoryResource;
    private \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper;
    private \M2E\TikTokShop\Model\Currency $currency;
    private ListingProductResource $listingProductResource;
    private \M2E\TikTokShop\Helper\Url $urlHelper;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Product\VariantSku */
    private ListingProductResource\VariantSku $productVariantResource;
    private \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource;
    private \M2E\TikTokShop\Model\ResourceModel\Shop $shopResource;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Promotion\Product $promotionProductResource;
    private \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Promotion $promotionResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource,
        \M2E\TikTokShop\Model\ResourceModel\Shop $shopResource,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        ListingProductResource $listingProductResource,
        \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary $categoryResource,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper,
        \M2E\TikTokShop\Model\ResourceModel\Promotion\Product $promotionProductResource,
        \M2E\TikTokShop\Model\Promotion\Product\Repository $promotionProductRepository,
        \M2E\TikTokShop\Model\ResourceModel\Promotion $promotionResource,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Model\Currency $currency,
        array $data = []
    ) {
        $this->categoryResource = $categoryResource;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->currency = $currency;
        $this->listingProductResource = $listingProductResource;
        $this->urlHelper = $urlHelper;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        $this->listingResource = $listingResource;
        $this->promotionProductResource = $promotionProductResource;
        $this->promotionProductRepository = $promotionProductRepository;
        $this->promotionResource = $promotionResource;
        parent::__construct(
            $uiListingRuntimeStorage,
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionDataHelper,
            $data
        );
        $this->shopResource = $shopResource;
        $this->productRepository = $productRepository;
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setDefaultSort(false);

        $this->setId('tikTokShopListingViewGrid' . $this->getListing()->getId());

        $this->showAdvancedFilterProductsOption = false;
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if (!$collection) {
            return $this;
        }

        $columnIndex = $column->getFilterIndex() ?: $column->getIndex();

        if ($columnIndex === self::COLUMN_INDEX_VARIANTS_PRICE) {
            if ($column->getDir() === 'asc') {
                $collection->getSelect()->order('online_min_price ASC');
            } else {
                $collection->getSelect()->order('online_max_price DESC');
            }

            return $this;
        }

        $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()));

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setItemObjectClass(Row::class);
        $collection->setListingProductModeOn(
            'listing_product',
            Row::KEY_LISTING_PRODUCT_ID
        );
        $collection->setStoreId($this->getListing()->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->joinTable(
            ['listing_product' => $this->listingProductResource->getMainTable()],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                Row::KEY_LISTING_PRODUCT_ID => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'product_id' => ListingProductResource::COLUMN_TTS_PRODUCT_ID,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'online_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_min_price' => ListingProductResource::COLUMN_ONLINE_MIN_PRICE,
                'online_max_price' => ListingProductResource::COLUMN_ONLINE_MAX_PRICE,
                'online_category' => ListingProductResource::COLUMN_ONLINE_CATEGORY,
                'template_category_id' => ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                'listing_id' => ListingProductResource::COLUMN_LISTING_ID,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->getListing()->getId()
            )
        );
        $collection->joinTable(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf('id = %s', ListingProductResource::COLUMN_LISTING_ID),
            [
                'shop_id' => 'shop_id',
            ]
        );

        $collection->joinTable(
            ['shop' => $this->shopResource->getMainTable()],
            'id = shop_id',
            [
                'shop_region' => 'region',
            ]
        );

        $collection->joinTable(
            ['category' => $this->categoryResource->getMainTable()],
            sprintf('id = %s', ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID),
            [
                'category_path' => 'path',
            ],
            null,
            'left'
        );

        $now = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        $select = $this->promotionProductResource->getConnection()->select();
        $select->from(
            $this->promotionProductResource->getMainTable(),
            [
                'promotion_product_id' => PromotionProductResource::COLUMN_PRODUCT_ID,
                'promotion_id' => PromotionProductResource::COLUMN_PROMOTION_ID,
            ]
        );
        $select->group('promotion_product_id');

        $collection->getSelect()->joinLeft(
            ['promotion_product' => new \Zend_Db_Expr('(' . $select . ')')],
            sprintf(
                '%s = promotion_product.promotion_product_id',
                ListingProductResource::COLUMN_TTS_PRODUCT_ID
            ),
            [
                'promotion_id' => 'promotion_id',
            ]
        );

        $collection->getSelect()->joinLeft(
            ['promotion' => $this->promotionResource->getMainTable()],
            'promotion.id = promotion_product.promotion_id',
            [
                'promotion_start_date' => PromotionResource::COLUMN_START_DATE,
                'promotion_end_date' => PromotionResource::COLUMN_END_DATE,
            ]
        );

        $collection->getSelect()->columns([
            'has_promotion' => new \Zend_Db_Expr(
                "IF(
            promotion_product.promotion_product_id IS NOT NULL
            AND promotion.start_date <= '{$now}'
            AND promotion.end_date >= '{$now}',
            true,
            false
        )"
            )
        ]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvListingGrid', __('CSV'));

        $this->addColumn('product_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'store_id' => $this->getListing()->getStoreId(),
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => __('Product Title / Product SKU'),
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'online_title',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('tik_tok_shop_product_id', [
            'header' => __('TikTok Shop Product ID'),
            'align' => 'left',
            'width' => '100',
            'type' => 'text',
            'index' => 'product_id',
            'renderer' => TtsProductIdRender::class,
        ]);

        $this->addColumn(
            'online_qty',
            [
                'header' => __('Available QTY'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'number',
                'index' => 'online_qty',
                'sortable' => true,
                'filter_index' => 'online_qty',
                'renderer' => \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\OnlineQty::class,
            ]
        );

        $priceColumn = [
            'header' => __('Price'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => self::COLUMN_INDEX_VARIANTS_PRICE,
            'sortable' => true,
            'frame_callback' => [$this, 'callbackColumnPrice'],
            'filter_condition_callback' => [$this, 'callbackFilterPrice'],
        ];

        $ttsProductIds = [];
        foreach ($this->getListing()->getProducts() as $ttsProduct) {
            $ttsProductIds[] = $ttsProduct->getTTSProductId();
        }

        if (
            $this->promotionProductRepository->isExistPromotionProductByProductIds($ttsProductIds)
        ) {
            $priceColumn['filter'] = \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Grid\Column\Filter\Price::class;
        }

        $this->addColumn('price', $priceColumn);

        if ($this->getListing()->getShop()->getRegion()->isRegionCodeUS()) {
            $this->addColumn('listing_quality', [
                'header' => __('Listing Quality'),
                'align' => 'left',
                'width' => '100',
                'type' => 'options',
                'options' => [
                    ProductListingQuality::TIER_POOR => ProductListingQuality::getTierLabel(
                        ProductListingQuality::TIER_POOR
                    ),
                    ProductListingQuality::TIER_FAIR => ProductListingQuality::getTierLabel(
                        ProductListingQuality::TIER_FAIR
                    ),
                    ProductListingQuality::TIER_GOOD => ProductListingQuality::getTierLabel(
                        ProductListingQuality::TIER_GOOD
                    ),
                ],
                'index' => 'listing_quality_tier',
                'frame_callback' => [$this, 'callbackColumnListingQuality'],
                'renderer' => \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\Status::class,
                'filter_condition_callback' => [$this, 'callbackFilterListingQuality'],
            ]);
        }

        $statusColumn = [
            'header' => __('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
                Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
                Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
                Product::STATUS_BLOCKED => Product::getStatusTitle(Product::STATUS_BLOCKED),
            ],
            'showLogIcon' => true,
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\Status::class,
            'filter_condition_callback' => [$this, 'callbackFilterStatus'],
        ];

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField(Row::KEY_LISTING_PRODUCT_ID);
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Configure groups
        // ---------------------------------------

        $groups = [
            'actions' => __('Listing Actions'),
            'other' => __('Other'),
        ];

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------

        $this->getMassactionBlock()->addItem('list', [
            'label' => __('List Item(s) on TikTok Shop'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('revise', [
            'label' => __('Revise Item(s) on TikTok Shop'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('relist', [
            'label' => __('Relist Item(s) on TikTok Shop'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stop', [
            'label' => __('Stop Item(s) on TikTok Shop'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', [
            'label' => __('Remove from TikTok Shop / Remove from Listing'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('sellOnAnotherMarket', [
            'label' => __('Sell on Another Market'),
            'url' => '',
        ], 'other');

        $this->getMassactionBlock()->addItem('moving', [
            'label' => $this->__('Move Item(s) to Another Listing'),
            'url' => '',
        ], 'other');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _afterLoadCollection()
    {
        /** @var Row[] $items */
        $items = $this->getCollection()->getItems();

        $listingProductIds = [];
        foreach ($items as $item) {
            $listingProductIds[] = $item->getListingProductId();
        }

        $products = $this->productRepository->findByIds($listingProductIds);

        $sortedProductsById = [];
        foreach ($products as $product) {
            $sortedProductsById[$product->getId()] = $product;
        }

        foreach ($items as $item) {
            $item->setListingProduct($sortedProductsById[$item->getListingProductId()] ?? null);
        }

        return parent::_afterLoadCollection();
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $row->getName();

        $onlineTitle = $row->getData('online_title');
        if (!empty($onlineTitle)) {
            $title = $onlineTitle;
        }

        $title = \M2E\TikTokShop\Helper\Data::escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');

        if ($row->getData('sku') === null) {
            $sku = $this->ourMagentoProductFactory
                ->createByProductId((int)$row->getData('entity_id'))
                ->getSku();
        }

        if ($isExport) {
            return \M2E\TikTokShop\Helper\Data::escapeHtml($sku);
        }

        $valueHtml .= '<br/>' .
            '<strong>' . __('SKU') . ':</strong>&nbsp;' .
            \M2E\TikTokShop\Helper\Data::escapeHtml($sku);

        if ($categoryId = $row->getData('online_category')) {
            $categoryPath = $row->getData('category_path');
            $categoryInfo = sprintf('%s (%s)', $categoryPath, $categoryId);
            $valueHtml .= '<br/><br/>' .
                '<strong>' . __('Category') . ':</strong>&nbsp;' .
                \M2E\TikTokShop\Helper\Data::escapeHtml($categoryInfo);
        }

        $listingProduct = $row->getListingProduct();

        if ($listingProduct === null || $listingProduct->isSimple()) {
            return $valueHtml;
        }

        $magentoProduct = $listingProduct->getMagentoProduct();
        $configurableAttributes = array_map(
            function (\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute) {
                return sprintf('<span>%s</span>', $attribute->getDefaultFrontendLabel());
            },
            $magentoProduct->getConfigurableAttributes()
        );

        $onclick = sprintf(
            'TikTokShopListingVariationProductManageObj.openPopUp(%s, \'%s\')',
            $listingProduct->getId(),
            $this->_escaper->escapeJs($magentoProduct->getName())
        );

        $manageLinkHtml = sprintf(
            '<a href="javascript:;" onclick="%s">%s</a>',
            $onclick,
            $this->__('Manage Variations')
        );

        $valueHtml .= sprintf(
            '<div class="m2e-salable-attribute-list"><p class="m2e-list">%s</p><p>%s</p></div>',
            implode(', ', $configurableAttributes),
            $manageLinkHtml
        );

        return $valueHtml;
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
                ['attribute' => 'online_title', 'like' => '%' . $value . '%'],
                ['attribute' => 'online_category', 'like' => '%' . $value . '%'],
            ]
        );
    }

    /**
     * @param $value
     * @param Row $row
     * @param $column
     * @param $isExport
     *
     * @return mixed|string
     */
    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return (string)$value;
        }

        $productStatus = $row->getData('status');

        if ((int)$productStatus === Product::STATUS_NOT_LISTED) {
            return sprintf(
                '<span style="color: gray;">%s</span>',
                __('Not Listed')
            );
        }

        $promotionHtml = '';
        if ($row['has_promotion']) {
            $promotionHtml =
                '<div class="fix-magento-tooltip on-promotion" style="float:right; text-align: left; margin-left: 5px;">' .
                '<div class="m2epro-field-tooltip admin__field-tooltip">' .
                '<a class="admin__field-tooltip-action" href="javascript://"></a>' .
                '<div class="admin__field-tooltip-content">' .
                'This Product is added to a promotion.' .
                '</div>' .
                '</div>' .
                '</div>';
        }

        $minPrice = $row->getData('online_min_price');
        $maxPrice = $row->getData('online_max_price');

        if ($minPrice === $maxPrice) {
            $price = $this->currency->formatPrice(
                $this->getListing()->getShop()->getCurrencyCode(),
                (float)$minPrice
            );

            return $price . $promotionHtml;
        }

        $formattedMinPrice = $this->currency->formatPrice(
            $this->getListing()->getShop()->getCurrencyCode(),
            (float)$minPrice
        );

        $formattedMaxPrice = $this->currency->formatPrice(
            $this->getListing()->getShop()->getCurrencyCode(),
            (float)$maxPrice
        );

        return sprintf('%s - %s', $formattedMinPrice, $formattedMaxPrice) . $promotionHtml;
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection $collection
     * @param $column
     *
     * @return void
     */
    protected function callbackFilterPrice($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();
        if (empty($condition)) {
            return;
        }

        $from = $condition['from'] ?? null;
        $to = $condition['to'] ?? null;

        $whereConditions = [];
        $now = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');

        if ($from === null || $to === null) {
            $value = $column->getFilter()->getValue();
            if (isset($value['on_promotion']) && $value['on_promotion'] !== '') {
                if ((int)$value['on_promotion'] === 1) {
                    $whereConditions[] = "promotion_product.promotion_product_id IS NOT NULL
                              AND promotion.start_date <= '{$now}'
                              AND promotion.end_date >= '{$now}'";
                } else {
                    $whereConditions[] = "promotion_product.promotion_product_id IS NULL
                              OR promotion.start_date > '{$now}'
                              OR promotion.end_date < '{$now}'";
                }
            }
        } else {
            if (!is_numeric($from)) {
                $from = PHP_INT_MIN;
            }
            if (!is_numeric($to)) {
                $to = PHP_INT_MAX;
            }

            $whereConditions[] = sprintf('%s <= online_max_price AND online_max_price <= %s', $from, $to);
            $whereConditions[] = sprintf('%s <= online_min_price AND online_min_price <= %s', $from, $to);
            $whereConditions[] = sprintf('online_min_price <= %s AND %s <= online_max_price', $from, $to);
        }

        $collection->getSelect()->where('(' . implode(' OR ', $whereConditions) . ')');

        $this->setCollection($collection);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value == null) {
            return;
        }

        if (is_array($value) && isset($value['value'])) {
            $collection->addFieldToFilter($index, (int)$value['value']);
        } else {
            if (!is_array($value) && $value !== null) {
                $collection->addFieldToFilter($index, (int)$value);
            }
        }
    }

    //region Column Listing Quality

    /**
     * @param $value
     * @param Row $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnListingQuality($value, $row, $column, $isExport)
    {
        $listingProduct = $row->getListingProduct();

        if ($listingProduct->isStatusNotListed()) {
            if ($isExport) {
                return '';
            }

            return sprintf(
                '<span style="color: gray;">%s</span>',
                __('Not Listed')
            );
        }

        if (!$listingProduct->isStatusListed()) {
            return __('N/A');
        }

        $listingQuality = $row->getListingProduct()->getListingQuality();

        if (
            $isExport
            || !$listingQuality->hasTier()
            || $listingQuality->isTierGood()
        ) {
            return ProductListingQuality::getTierLabel($listingQuality->getTier());
        }

        return sprintf(
            '<span style="color:%s" data-product-id="%s" %s>%s</span>',
            $listingQuality->isTierPoor() ? '#e62e00' : '#e69900',
            $row->getListingProduct()->getId(),
            $listingQuality->hasRecommendations() ? 'data-has-recommendations' : '',
            ProductListingQuality::getTierLabel($listingQuality->getTier())
        );
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection $collection
     * @param $column
     *
     * @return void
     */
    public function callbackFilterListingQuality($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (empty($value)) {
            return;
        }

        $collection->getSelect()->where(
            sprintf(
                "listing_product.%s = '%s' AND listing_product.%s = %s",
                \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_LISTING_QUALITY_TIER,
                $value,
                \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_STATUS,
                \M2E\TikTokShop\Model\Product::STATUS_LISTED,
            )
        );
    }

    //endregion

    public function getGridUrl()
    {
        return $this->getUrl('*/tiktokshop_listing/view', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function getTooltipHtml(string $content, $id = false): string
    {
        return <<<HTML
<div id="$id" class="TikTokShop-field-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content" style="">
        {$content}
    </div>
</div>
HTML;
    }

    protected function _beforeToHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add("TikTokShopListingViewTikTokShopGridObj.afterInitPage()");

            return parent::_beforeToHtml();
        }

        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $this->getId();
        $ignoreListings = \M2E\TikTokShop\Helper\Json::encode([$this->getListing()->getId()]);

        $this->jsUrl->addUrls([
            'runListProducts' => $this->getUrl('*/tiktokshop_listing/runListProducts'),
            'runRelistProducts' => $this->getUrl('*/tiktokshop_listing/runRelistProducts'),
            'runReviseProducts' => $this->getUrl('*/tiktokshop_listing/runReviseProducts'),
            'runStopProducts' => $this->getUrl('*/tiktokshop_listing/runStopProducts'),
            'runStopAndRemoveProducts' => $this->getUrl('*/tiktokshop_listing/runStopAndRemoveProducts'),
            'previewItems' => $this->getUrl('*/tiktokshop_listing/previewItems'),
            'getListingQualityRecommendation' => $this->getUrl(
                '*/listing_product_view/getListingQualityRecommendation'
            ),
        ]);

        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListingGrid'), 'moveToListingGridHtml');
        $this->jsUrl->add($this->getUrl('*/listing_moving/prepareMoveToListing'), 'prepareData');
        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListing'), 'moveToListing');

        $this->jsUrl->addUrls([
            'runSellOnAnotherMarker' => $this->getUrl('*/tiktokshop_listing/runSellOnAnotherMarker'),
            'sellOnAnotherMarker/selectListing' => $this->getUrl(
                '*/listing_sellOnAnotherMarket/selectListing',
                [
                    'listing_id' => $this->getListing()->getId()
                ]
            ),
        ]);

        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop_log_listing_product/index'),
            'tiktokshop_log_listing_product/index'
        );

        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop_log_listing_product/index', [
                AbstractGrid::LISTING_ID_FIELD => $this->getListing()->getId(),
                'back' => $this->urlHelper->makeBackUrlParam(
                    '*/tiktokshop_listing/view',
                    ['id' => $this->getListing()->getId()]
                ),
            ]),
            'logViewUrl'
        );
        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop_listing_moving/moveToListingGrid'),
            'tiktokshop_listing_moving/moveToListingGrid'
        );

        $taskCompletedWarningMessage = __(
            '"%task_title%" task has completed with warnings. ' .
            '<a target="_blank" href="%url%">View Log</a> for details.'
        );

        $taskCompletedErrorMessage = __(
            '"%task_title%" task has completed with errors. ' .
            '<a target="_blank" href="%url%">View Log</a> for details.'
        );

        $this->jsTranslator->addTranslations([
            'task_completed_message' => __('Task completed. Please wait ...'),
            'task_completed_success_message' => __('"%task_title%" task has completed.'),
            'task_completed_warning_message' => $taskCompletedWarningMessage,
            'task_completed_error_message' => $taskCompletedErrorMessage,
            'sending_data_message' => __('Sending %product_title% Product(s) data on TikTok Shop.'),
            'view_full_product_log' => __('View Full Product Log.'),
            'listing_selected_items_message' => __('Listing Selected Items On TikTok Shop'),
            'revising_selected_items_message' => __('Revising Selected Items On TikTok Shop'),
            'relisting_selected_items_message' => __('Relisting Selected Items On TikTok Shop'),
            'stopping_selected_items_message' => __('Stopping Selected Items On TikTok Shop'),
            'stopping_and_removing_selected_items_message' => __(
                'Removing from TikTok Shop And Removing From Listing Selected Items'
            ),
            'removing_selected_items_message' => __('Removing From Listing Selected Items'),

            'Please select the Products you want to perform the Action on.' =>
                __('Please select the Products you want to perform the Action on.'),
            'Please select Action.' => __('Please select Action.'),
            'Specifics' => __('Specifics'),
        ]);

        $this->js->add(
            <<<JS
    TikTokShop.productsIdsForList = '$productsIdsForList';
    TikTokShop.customData.gridId = '$gridId';
    TikTokShop.customData.ignoreListings = '$ignoreListings';
JS
        );

        $openPopUpWithFilterJs = '';
        if ($childVariationIds = $this->getRequest()->getParam('child_variation_ids')) {
            $openPopUpWithFilterJs = <<<JS
function openPopupWithFilter() {
    const checkboxes = $$('#$gridId .col-select input.admin__control-checkbox');
    const titles = $$('#$gridId .product-title-value');

    if (checkboxes.length !== 1) {
        return;
    }

    const firstItemId = checkboxes[0].value;
    const firstItemTitle = titles[0].innerText;

    TikTokShopListingVariationProductManageObj.openPopUp(firstItemId, firstItemTitle, '$childVariationIds');
}

openPopupWithFilter();

JS;
        }

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Listing/View/TikTokShop/Grid',
        'TikTokShop/TikTokShop/Listing/VariationProductManage',
        'TikTokShop/Listing/View/ListingQuality',
    ], function() {
        window.TikTokShopListingVariationProductManageObj = new TikTokShopListingVariationProductManage()
        window.TikTokShopListingViewTikTokShopGridObj = new TikTokShopListingViewTikTokShopGrid('$gridId', {$this->getListing(
            )->getId()});

        TikTokShopListingViewTikTokShopGridObj.afterInitPage();

        TikTokShopListingViewTikTokShopGridObj.actionHandler.setProgressBar('listing_view_progress_bar');
        TikTokShopListingViewTikTokShopGridObj.actionHandler.setGridWrapper('listing_view_content_container');

        TikTokShopListingViewTikTokShopGridObj.movingHandler.setProgressBar('listing_view_progress_bar');
        TikTokShopListingViewTikTokShopGridObj.movingHandler.setGridWrapper('listing_view_content_container');

        if (TikTokShop.productsIdsForList) {
            TikTokShopListingViewTikTokShopGridObj.getGridMassActionObj().checkedString = TikTokShop.productsIdsForList;
            TikTokShopListingViewTikTokShopGridObj.actionHandler.listAction();
        }

        {$openPopUpWithFilterJs}
    });
JS
        );

        return parent::_beforeToHtml();
    }
}
