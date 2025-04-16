<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\Settings;

use M2E\TikTokShop\Model\ResourceModel\Category\Dictionary as CategoryDictionaryResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Listing\View\AbstractGrid
{
    private CategoryDictionaryResource $categoryResource;
    private \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper;
    private ListingProductResource $listingProductResource;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory,
        ListingProductResource $listingProductResource,
        CategoryDictionaryResource $categoryResource,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->categoryResource = $categoryResource;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->urlHelper = $urlHelper;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingProductResource = $listingProductResource;
        $this->magentoProductFactory = $magentoProductFactory;

        parent::__construct(
            $uiListingRuntimeStorage,
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionDataHelper,
            $data
        );
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('tikTokShopListingViewGrid' . $this->getListing()->getId());

        $this->css->addFile('tiktokshop/template.css');
        $this->css->addFile('listing/grid.css');

        $this->showAdvancedFilterProductsOption = false;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection(): Grid
    {
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->setListingProductModeOn('lp', ListingProductResource::COLUMN_ID);
        $collection->setStoreId($this->getListing()->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $lpTable = $this->listingProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $lpTable],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'available_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_category' => ListingProductResource::COLUMN_ONLINE_CATEGORY,
                'template_category_id' => ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->getListing()->getId()
            )
        );

        $categoryTableName = $this->categoryResource->getMainTable();
        $collection
            ->joinTable(
                ['category' => $categoryTableName],
                sprintf('%s = template_category_id', CategoryDictionaryResource::COLUMN_ID),
                [
                    'path' => CategoryDictionaryResource::COLUMN_PATH,
                    'category_id' => CategoryDictionaryResource::COLUMN_CATEGORY_ID,
                    'is_valid' => CategoryDictionaryResource::COLUMN_IS_VALID,
                ],
                null,
                'left'
            );

        $this->setCollection($collection);

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
                'header' => __('Product ID'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'number',
                'index' => 'entity_id',
                'store_id' => $this->getListing()->getStoreId(),
                'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product Title / Product SKU'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'name',
                'escape' => false,
                'frame_callback' => [$this, 'callbackColumnTitle'],
                'filter_condition_callback' => [$this, 'callbackFilterTitle'],
            ]
        );

        $this->addColumn(
            'category',
            [
                'header' => __('TikTok Category'),
                'align' => 'left',
                'width' => '200px',
                'type' => 'text',
                'frame_callback' => [$this, 'callbackColumnCategory'],
                'filter_condition_callback' => [$this, 'callbackFilterCategory'],
            ]
        );

        $this->addColumn('actions', [
            'header' => $this->__('Actions'),
            'align' => 'left',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'field' => 'id',
            'group_order' => $this->getGroupOrder(),
            'actions' => $this->getColumnActionsItems(),
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->setGroups([
            'edit_categories_settings' => $this->__('Edit Category Settings'),
            'other' => $this->__('Other'),
        ]);

        $this->getMassactionBlock()->addItem('editCategorySettings', [
            'label' => $this->__('Categories & Specifics'),
            'url' => '',
        ], 'edit_categories_settings');

        return $this;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport): string
    {
        $value = '<span>' . \M2E\Core\Helper\Data::escapeHtml($value) . '</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = $this->magentoProductFactory
                ->createByProductId((int)$row->getData('entity_id'))
                ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') . ':</strong>&nbsp;';
        $value .= \M2E\Core\Helper\Data::escapeHtml($sku);

        return $value;
    }

    public function callbackColumnCategory($value, $row, $column, $isExport): string
    {
        $categoryId = $row->getData('category_id') ?? '';
        $path = $row->getData('path') ?? '';
        if (empty($categoryId) && empty($path)) {
            return sprintf(
                '<span style="color: #e22626;">%s</span>',
                __('Not Set')
            );
        }

        $view = sprintf('%s (%s)', $path, $categoryId);

        if (!$row->getData('is_valid')) {
            return sprintf(
                '<div><p style="padding: 2px 0 0 10px">%s <span style="color: #f00;">%s</span></p></span>',
                $view,
                __('Invalid')
            );
        }

        return sprintf(
            '<div><p style="padding: 2px 0 0 10px">%s</p></span>',
            $view
        );
    }

    public function callbackFilterTitle($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue();

        if ($inputValue !== null) {
            $fieldsToFilter = [
                ['attribute' => 'sku', 'like' => '%' . $inputValue . '%'],
                ['attribute' => 'name', 'like' => '%' . $inputValue . '%'],
            ];

            $collection->addFieldToFilter($fieldsToFilter);
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\MSI\Magento\Product\Collection $collection
     * @param \M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite $column
     *
     * @return void
     */
    public function callbackFilterCategory($collection, $column)
    {
        $filter = $column->getFilter();

        if ($value = $filter->getValue()) {
            $collection->getSelect()->where(
                new \Zend_Db_Expr(
                    sprintf(
                        "CONCAT(%s, ' (', %s, ')') LIKE %s",
                        CategoryDictionaryResource::COLUMN_PATH,
                        CategoryDictionaryResource::COLUMN_CATEGORY_ID,
                        $collection->getConnection()->quote("%$value%"),
                    )
                )
            );
        }
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/tiktokshop_listing/view', ['_current' => true]);
    }

    public function getRowUrl($item): bool
    {
        return false;
    }

    protected function getGroupOrder(): array
    {
        return [
            'edit_categories_settings' => $this->__('Edit Category Settings'),
        ];
    }

    protected function getColumnActionsItems(): array
    {
        $actions = [
            'editCategories' => [
                'caption' => $this->__('Categories & Attributes'),
                'group' => 'edit_categories_settings',
                'field' => 'id',
                'onclick_action' => "TikTokShopListingViewSettingsGridObj.actions['editCategorySettingsAction']",
            ],
        ];

        return $actions;
    }

    protected function _beforeToHtml()
    {
        $this->js->add(
            <<<JS
 require([
     'TikTokShop/Category/Chooser/SelectedProductsData'
], function() {
     window.SelectedProductsDataObj = new SelectedProductsData();
     SelectedProductsDataObj.setShopId('{$this->getListing()->getShopId()}');
});
JS,
        );

        return parent::_beforeToHtml();
    }

    protected function _toHtml(): string
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
            TikTokShopListingViewSettingsGridObj.afterInitPage();
JS
            );

            return parent::_toHtml();
        }

        $this->jsUrl->add(
            $this->getUrl('*/listing_product_category_settings/edit', ['_current' => true]),
            'listing_product_category_settings/edit'
        );
        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop_listing/saveCategoryTemplate', [
                'listing_id' => $this->getListing()->getId(),
            ]),
            'tiktokshop_listing/saveCategoryTemplate'
        );

        //------------------------------
        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $ignoreListings = \M2E\Core\Helper\Json::encode([$this->getListing()->getId()]);

        $this->js->add(
            <<<JS
    TikTokShop.productsIdsForList = '$productsIdsForList';
    TikTokShop.customData.gridId = '{$this->getId()}';
    TikTokShop.customData.ignoreListings = '{$ignoreListings}';
JS
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Listing/View/Settings/Grid',
    ], function(){

        window.TikTokShopListingViewSettingsGridObj = new TikTokShopListingViewSettingsGrid(
            '{$this->getId()}',
            '{$this->getListing()->getId()}',
            '{$this->getListing()->getAccountId()}',
            '{$this->getListing()->getShopId()}'
        );
        TikTokShopListingViewSettingsGridObj.afterInitPage();
    });
JS
        );

        return parent::_toHtml();
    }
}
