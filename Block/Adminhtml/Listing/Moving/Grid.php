<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Moving;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \Magento\Store\Model\StoreFactory $storeFactory;
    private \M2E\TikTokShop\Helper\View $viewHelper;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->storeFactory = $storeFactory;
        $this->viewHelper = $viewHelper;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $backendHelper, $data);
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingMovingGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(100);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $ignoreListings = (array)$this->globalDataHelper->getValue('ignoreListings');

        $collection = $this->listingCollectionFactory->create();

        foreach ($ignoreListings as $listingId) {
            $collection->addFieldToFilter('main_table.id', ['neq' => $listingId]);
        }

        $this->addAccountAndShopFilter($collection);

        $collection->addProductsTotalCount();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('listing_id', [
            'header' => __('ID'),
            'align' => 'right',
            'type' => 'number',
            'width' => '75px',
            'index' => 'id',
            'filter_index' => 'id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'width' => '200px',
            'index' => 'title',
            'escape' => false,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
        ]);

        $this->addColumn('store_name', [
            'header' => __('Store View'),
            'align' => 'left',
            'type' => 'text',
            'width' => '100px',
            'index' => 'store_id',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnStore'],
        ]);

        $this->addColumn('products_total_count', [
            'header' => __('Total Items'),
            'align' => 'right',
            'type' => 'number',
            'width' => '100px',
            'index' => 'products_total_count',
            'filter_index' => 'products_total_count',
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

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = \M2E\TikTokShop\Helper\Data::escapeHtml($value);
        $url = $this->viewHelper->getUrl(
            $row,
            'listing',
            'view',
            ['id' => $row->getData('id')]
        );

        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $title);
    }

    public function callbackColumnStore($value, $row, $column, $isExport)
    {
        $storeModel = $this->storeFactory->create()->load($value);
        $website = $storeModel->getWebsite();

        if (!$website) {
            return '';
        }

        $websiteName = $website->getName();

        if (strtolower($websiteName) != 'admin') {
            $storeName = $storeModel->getName();
        } else {
            $storeName = $storeModel->getGroup()->getName();
        }

        return $storeName;
    }

    public function callbackColumnSource($value, $row, $column, $isExport)
    {
        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $moveText = __('Move To This Listing');
        return <<<HTML
&nbsp;<a href="javascript:void(0);" onclick="CommonObj.confirm({
        actions: {
            confirm: function () {
                {$this->getMovingHandlerJs()}.gridHandler.tryToMove({$row->getData('id')});
            }.bind(this),
            cancel: function () {
                return false;
            }
        }
    });">$moveText</a>
HTML;
    }

    protected function getHelpBlockHtml()
    {
        $helpBlockHtml = '';

        if ($this->canDisplayContainer()) {
            $helpBlockHtml = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)->setData(
                [
                    'content' => __('Item(s) can be moved to a Listing within the same TikTok Shop Account and Shop.<br> ' .
                        'You can select an existing M2E TikTok Shop Connect Listing or create a new one.<br><br>' .
                        '<strong>Note:</strong> Once the Items are moved, they will be updated based ' .
                        'on the new Listing settings.'),
                ]
            )->toHtml();
        }

        return $helpBlockHtml;
    }

    protected function getNewListingUrl(): string
    {
        return $this->getUrl(
            '*/tiktokshop_listing_create/index',
            [
                'step' => 1,
                'clear' => 1,
                'account_id' => $this->globalDataHelper->getValue('accountId'),
                'shop_id' => $this->globalDataHelper->getValue('shopId'),
                'creation_mode' => \M2E\TikTokShop\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY,
            ]
        );
    }

    protected function _toHtml()
    {
        $this->jsUrl->add($this->getNewListingUrl(), 'add_new_listing_url');

        $this->js->add(
            <<<JS
        var warning_msg_block = $('empty_grid_warning');
            warning_msg_block && warning_msg_block.remove();

            $$('#listingMovingGrid div.grid th').each(function(el) {
                el.style.padding = '2px 4px';
            });

            $$('#listingMovingGrid div.grid td').each(function(el) {
                el.style.padding = '2px 4px';
            });
JS
        );

        return $this->getHelpBlockHtml() . parent::_toHtml();
    }

    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function addAccountAndShopFilter($collection)
    {
        $accountId = $this->globalDataHelper->getValue('accountId');
        $shopId = $this->globalDataHelper->getValue('shopId');

        $collection->addFieldToFilter('shop_id', $shopId);
        $collection->addFieldToFilter('account_id', $accountId);
    }
}
