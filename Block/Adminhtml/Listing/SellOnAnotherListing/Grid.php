<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\SellOnAnotherListing;

use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\TikTokShop\Model\Listing $sourceListing;
    private \Magento\Store\Model\StoreFactory $storeFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \Magento\Backend\Model\UrlInterface $urlBuilder;

    public function __construct(
        \M2E\TikTokShop\Model\Listing $sourceListing,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->sourceListing = $sourceListing;
        $this->storeFactory = $storeFactory;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingSellOnAnotherMarketMovingGrid');
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
        $collection->addFieldToFilter(
            ListingResource::COLUMN_SHOP_ID,
            ['neq' => $this->sourceListing->getShopId()]
        );
        $collection->addFieldToFilter(
            ListingResource::COLUMN_ACCOUNT_ID,
            ['eq' => $this->sourceListing->getAccountId()]
        );

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
            'index' => 'id',
            'filter_index' => 'id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => false,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
        ]);

        $this->addColumn('store_name', [
            'header' => __('Store View'),
            'align' => 'left',
            'type' => 'text',
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
            'frame_callback' => [$this, 'callbackColumnTotal'],
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

    /**
     * @param \M2E\TikTokShop\Model\Listing $row
     */
    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = \M2E\TikTokShop\Helper\Data::escapeHtml($value);
        $url = $this->urlBuilder->getUrl("*/tiktokshop_listing/view", ['id' => $row->getData('id')]);

        $html = '<div>';
        $html .= sprintf('<a href="%s" target="_blank">%s</a>', $url, $title);
        $html .= sprintf(
            '<p><strong>%s</strong>: %s</p>',
            __('Account'),
            $row->getAccount()->getTitle()
        );
        $html .= sprintf(
            '<p><strong>%s</strong>: %s</p>',
            __('Shop'),
            $row->getShop()->getShopNameWithRegion()
        );
        $html .= '</div>';

        return $html;
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

    /**
     * @param \M2E\TikTokShop\Model\Listing $row
     */
    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        return sprintf(
            '<a href="javascript:void(0);" class="choice-listing"' .
            ' data-source-listing-id="%s"' .
            ' data-target-listing-id="%s">%s</a>',
            $this->sourceListing->getId(),
            $row->getId(),
            __('List Item(s)')
        );
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        return $row->getData('products_total_count') ?? 0;
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/listing_sellOnAnotherMarket/selectListing',
            ['_current' => true]
        );
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->addUrls([
            'sellOnAnotherMarker/moveProducts' => $this->getUrl(
                '*/listing_sellOnAnotherMarket/moveProducts'
            ),
            'sellOnAnotherMarker/createNewListing' => $this->getUrl(
                '*/tiktokshop_listing_create/index',
                [
                    'step' => 1,
                    'clear' => 1,
                    'creation_mode' => \M2E\TikTokShop\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY,
                    'wizard' => true,
                    'account_id' => $this->sourceListing->getAccountId()
                ]
            ),
            'sellOnAnotherMarker/targetListingUrl' => $this->getUrl('*/tiktokshop_listing/view'),
        ]);

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $hidden = sprintf(
            '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" disabled>',
            'account_id',
            $this->sourceListing->getId()
        );

        $hidden .= sprintf(
            '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" disabled>',
            'grid_object',
            $this->getJsObjectName()
        );

        return $this->getHelpBlockHtml()
                . $hidden
                . parent::_toHtml();
    }

    private function getHelpBlockHtml()
    {
        $helpBlockHtml = '';

        if ($this->canDisplayContainer()) {
            $helpContent = __('To publish a Global Product in a new TikTok Shop market, move the Item(s) ' .
                'to a Listing within the same TikTok Shop Account and Shop, but with a different target region.<br>' .
                'You can choose an existing M2E TikTok Shop Connect Listing or create a new one to efficiently ' .
                'manage your products across multiple TikTok Shop markets.');

            $helpBlockHtml = $this
                ->getLayout()
                ->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)
                ->setData(['content' => $helpContent])
                ->toHtml();
        }

        return $helpBlockHtml;
    }
}
