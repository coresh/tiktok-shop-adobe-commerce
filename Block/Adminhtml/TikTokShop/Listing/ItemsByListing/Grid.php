<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByListing;

use M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Listing\Grid
{
    private \M2E\TikTokShop\Model\ResourceModel\Account $accountResource;
    private \M2E\TikTokShop\Model\ResourceModel\Shop $shopResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource;
    /** @var \M2E\TikTokShop\Helper\Url */
    private $urlHelper;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory */
    private $listingCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource,
        \M2E\TikTokShop\Model\ResourceModel\Account $accountResource,
        \M2E\TikTokShop\Model\ResourceModel\Shop $shopResource,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        array $data = []
    ) {
        parent::__construct($urlHelper, $viewHelper, $context, $backendHelper, $dataHelper, $data);

        $this->accountResource = $accountResource;
        $this->shopResource = $shopResource;
        $this->listingProductResource = $listingProductResource;
        $this->urlHelper = $urlHelper;
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    /**
     * @ingeritdoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('tikTokShopListingItemsByListingGrid');
    }

    /**
     * @ingeritdoc
     */
    public function getRowUrl($item)
    {
        return $this->getUrl(
            '*/tiktokshop_listing/view',
            [
                'id' => $item->getId(),
                'back' => $this->getBackUrl(),
            ]
        );
    }

    /**
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->urlHelper->makeBackUrlParam('*/tiktokshop_listing/index');
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByListing\Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $collection = $this->listingCollectionFactory->create();
        $collection->getSelect()->join(
            ['account' => $this->accountResource->getMainTable()],
            sprintf(
                'account.%s = main_table.%s',
                \M2E\TikTokShop\Model\ResourceModel\Account::COLUMN_ID,
                \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_ACCOUNT_ID
            ),
            ['account_title' => \M2E\TikTokShop\Model\ResourceModel\Account::COLUMN_TITLE]
        );
        $collection->getSelect()->join(
            ['shop' => $this->shopResource->getMainTable()],
            sprintf(
                'shop.%s = main_table.%s',
                \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_ID,
                \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_SHOP_ID
            ),
            [
                'shop_name' => \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_SHOP_NAME,
                'shop_region_code' => \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_REGION,
            ]
        );

        $select = $collection->getConnection()->select();
        $select->from(['lp' => $this->listingProductResource->getMainTable()], [
            'listing_id' => \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_LISTING_ID,
            'products_total_count' => new \Zend_Db_Expr(
                sprintf(
                    'COUNT(lp.%s)',
                    \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_ID
                )
            ),
            'products_active_count' => new \Zend_Db_Expr(
                sprintf(
                    'COUNT(IF(lp.%s = %s, lp.%s, NULL))',
                    \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_STATUS,
                    \M2E\TikTokShop\Model\Product::STATUS_LISTED,
                    \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_ID
                )
            ),
            'products_inactive_count' => new \Zend_Db_Expr(
                sprintf(
                    'COUNT(IF(lp.%s != %s, lp.%s, NULL))',
                    \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_STATUS,
                    \M2E\TikTokShop\Model\Product::STATUS_LISTED,
                    \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_ID
                )
            ),
        ]);
        $select->group(
            sprintf(
                'lp.%s',
                \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_LISTING_ID
            )
        );

        $collection->getSelect()->joinLeft(
            ['t' => $select],
            'main_table.id = t.listing_id',
            [
                'products_total_count' => 'products_total_count',
                'products_active_count' => 'products_active_count',
                'products_inactive_count' => 'products_inactive_count',
            ]
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @ingeritdoc
     */
    protected function _prepareLayout()
    {
        $this->css->addFile('listing/grid.css');

        return parent::_prepareLayout();
    }

    /**
     * @return array[]
     */
    protected function getColumnActionsItems()
    {
        $backUrl = $this->getBackUrl();

        return [
            'manageProducts' => [
                'caption' => __('Manage'),
                'group' => 'products_actions',
                'field' => 'id',
                'url' => [
                    'base' => '*/tiktokshop_listing/view',
                    'params' => [
                        'id' => $this->getId(),
                        'back' => $backUrl,
                    ],
                ],
            ],

            'addProductsSourceProducts' => [
                'caption' => __('Add From Products List'),
                'group' => 'products_actions',
                'field' => 'id',
                'onclick_action' => 'TikTokShopListingGridObj.addProductsSourceProductsAction',
            ],

            'addProductsSourceCategories' => [
                'caption' => __('Add From Categories'),
                'group' => 'products_actions',
                'field' => 'id',
                'onclick_action' => 'TikTokShopListingGridObj.addProductsSourceCategoriesAction',
            ],

            'editTitle' => [
                'caption' => __('Title'),
                'group' => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EditListingTitleObj.openPopup',
            ],

            'editStoreView' => [
                'caption' => __('Store View'),
                'group' => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EditListingStoreViewObj.openPopup',
            ],

            'editConfiguration' => [
                'caption' => __('Configuration'),
                'group' => 'edit_actions',
                'field' => 'id',
                'url' => [
                    'base' => '*/tiktokshop_listing/edit',
                    'params' => ['back' => $backUrl],
                ],
            ],

            'viewLogs' => [
                'caption' => __('Logs & Events'),
                'group' => 'other',
                'field' => \M2E\TikTokShop\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
                'url' => [
                    'base' => '*/tiktokshop_log_listing_product/index',
                ],
            ],

            'clearLogs' => [
                'caption' => __('Clear Log'),
                'confirm' => __('Are you sure?'),
                'group' => 'other',
                'field' => 'id',
                'url' => [
                    'base' => '*/listing/clearLog',
                    'params' => [
                        'back' => $backUrl,
                    ],
                ],
            ],

            'delete' => [
                'caption' => __('Delete Listing'),
                'confirm' => __('Are you sure?'),
                'group' => 'other',
                'field' => 'id',
                'url' => [
                    'base' => '*/tiktokshop_listing/delete',
                    'params' => ['id' => $this->getId()],
                ],
            ],
        ];
    }

    /**
     * editPartsCompatibilityMode has to be not accessible for not Multi Motors marketplaces
     * @return $this
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        $this->getColumn('actions')->setData(
            'renderer',
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Grid\Column\Renderer\Action::class
        );

        return $result;
    }

    /**
     * @param string $value
     * @param \M2E\TikTokShop\Model\Listing $row
     * @param Rewrite $column
     * @param bool $isExport
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = \M2E\TikTokShop\Helper\Data::escapeHtml($value);

        $value = <<<HTML
<span id="listing_title_{$row->getId()}">
    {$title}
</span>
HTML;

        $accountTitle = $row->getData('account_title');
        $shopTitle = $row->getData('shop_name');
        $shopRegionTitle = \M2E\TikTokShop\Model\Shop::getRegionNameByCode($row->getData('shop_region_code') ?? '');

        $storeModel = $this->_storeManager->getStore($row->getStoreId());
        $storeView = $this->_storeManager->getWebsite($storeModel->getWebsiteId())->getName();
        if (strtolower($storeView) != 'admin') {
            $storeView .= ' > ' . $this->_storeManager->getGroup($storeModel->getStoreGroupId())->getName();
            $storeView .= ' > ' . $storeModel->getName();
        } else {
            $storeView = __('Admin (Default Values)');
        }

        $account = __('Account');
        $shop = __('Shop');
        $shopRegion = __('Region');
        $store = __('Magento Store View');

        $value .= <<<HTML
<div>
    <span style="font-weight: bold">$account</span>: <span style="color: #505050">$accountTitle</span><br/>
    <span style="font-weight: bold">$shop</span>: <span style="color: #505050">$shopTitle</span><br/>
    <span style="font-weight: bold">$shopRegion</span>: <span style="color: #505050">$shopRegionTitle</span><br/>
    <span style="font-weight: bold">$store</span>: <span style="color: #505050">$storeView</span>
</div>
HTML;

        return $value;
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\Listing\Collection $collection
     * @param Rewrite $column
     *
     * @return void
     */
    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR account.title LIKE ? OR shop.shop_name LIKE ?',
            '%' . $value . '%'
        );
    }

    /**
     * @ingeritdoc
     */
    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $this->jsUrl->addUrls(
            array_merge(
                $this->dataHelper->getControllerActions('TikTokShop\Listing'),
                $this->dataHelper->getControllerActions('TikTokShop_Log_Listing_Product'),
                $this->dataHelper->getControllerActions('TikTokShop\Template')
            )
        );

        $this->jsUrl->add($this->getUrl('*/listing/edit'), 'listing/edit');

        $this->jsUrl->add($this->getUrl('*/tiktokshop_listing_edit/selectStoreView'), 'listing/selectStoreView');
        $this->jsUrl->add($this->getUrl('*/tiktokshop_listing_edit/saveStoreView'), 'listing/saveStoreView');

        $this->jsTranslator->add('Edit Listing Title', __('Edit Listing Title'));
        $this->jsTranslator->add('Edit Listing Store View', __('Edit Listing Store View'));
        $this->jsTranslator->add('Listing Title', __('Listing Title'));
        $this->jsTranslator->add(
            'The specified Title is already used for other Listing. Listing Title must be unique.',
            __(
                'The specified Title is already used for other Listing. Listing Title must be unique.'
            )
        );

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Helper\Component\TikTokShop::class)
        );

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Listing/Grid',
        'TikTokShop/Listing/EditTitle',
        'TikTokShop/Listing/EditStoreView'
    ], function(){
        window.TikTokShopListingGridObj = new TikTokShopListingGrid('{$this->getId()}');
        window.EditListingTitleObj = new ListingEditListingTitle('{$this->getId()}');
        window.EditListingStoreViewObj = new ListingEditListingStoreView('{$this->getId()}');
    });
JS
        );

        return parent::_toHtml();
    }
}
