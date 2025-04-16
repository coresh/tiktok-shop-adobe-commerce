<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Settings\MoveFromListing;

use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private int $ignoreListing;
    private \Magento\Store\Model\StoreFactory $storeFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        int $ignoreListing,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->ignoreListing = $ignoreListing;
        $this->storeFactory = $storeFactory;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->listingRepository = $listingRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingSettingsMovingGrid');
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
        $collection = $this->listingCollectionFactory->create();
        $collection->addFieldToFilter('main_table.id', ['neq' => $this->ignoreListing]);
        $ignoreListing = $this->listingRepository->get($this->ignoreListing);
        $collection->addFieldToFilter(ListingResource::COLUMN_ACCOUNT_ID, $ignoreListing->getAccountId());
        $collection->addFieldToFilter(ListingResource::COLUMN_SHOP_ID, $ignoreListing->getShopId());

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
        $title = \M2E\Core\Helper\Data::escapeHtml($value);
        $url = $this->urlBuilder->getUrl("*/tiktokshop_listing/view", ['id' => $row->getData('id')]);

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

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $moveText = __('Move To This Listing');

        return <<<HTML
&nbsp;<a href="javascript:void(0);" onclick="CommonObj.confirm({
        actions: {
            confirm: function () {
                TikTokShopListingViewTikTokShopGridObj.movingHandler.gridHandler.tryToMove({$row->getData('id')});
            }.bind(this),
            cancel: function () {
                return false;
            }
        }
    });">$moveText</a>
HTML;
    }

    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function getHelpBlockHtml()
    {
        $helpBlockHtml = '';

        if ($this->canDisplayContainer()) {
            $helpBlockHtml = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)->setData(
                [
                    'content' => __(
                        'Item(s) can be moved to a Listing within the same %channel_title Account
                        and Shop.<br>You can select an existing %extension_title Listing or create a new one.<br><br>
                        <strong>Note:</strong> Once the Items are moved, they will be updated based on the
                        new Listing settings.',
                        [
                            'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                            'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                        ]
                    ),
                ]
            )->toHtml();
        }

        return $helpBlockHtml;
    }

    protected function _toHtml()
    {
        $this->jsUrl->add($this->getNewListingUrl(), 'add_new_listing_url');

        $this->js->add(
            <<<JS
        const warning_msg_block = $('empty_grid_warning');
            warning_msg_block && warning_msg_block.remove();

            $$('#listingSettingsMovingGrid div.grid th').each(function(el) {
                el.style.padding = '2px 4px';
            });

            $$('#listingSettingsMovingGrid div.grid td').each(function(el) {
                el.style.padding = '2px 4px';
            });
JS
        );

        return $this->getHelpBlockHtml() . parent::_toHtml();
    }

    private function getNewListingUrl(): string
    {
        return $this->getUrl(
            '*/tiktokshop_listing_create/index',
            [
                'step' => 1,
                'clear' => 1,
                'account_id' => $this->listingRepository->get($this->ignoreListing)->getAccountId(),
                'shop_id' => $this->listingRepository->get($this->ignoreListing)->getShopId(),
                'creation_mode' => \M2E\TikTokShop\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY,
            ]
        );
    }
}
