<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByIssue;

use M2E\TikTokShop\Block\Adminhtml\Tag\Switcher as TagSwitcher;
use M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite;
use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    /** @var \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation\CollectionFactory */
    private $relationCollectionFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Tag */
    private $tagResource;
    /** @var ListingResource */
    private $listingResource;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Product */
    private $listingProductResource;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory */
    private $listingProductCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation\CollectionFactory $relationCollectionFactory,
        ListingResource                                                                   $listingResource,
        \M2E\TikTokShop\Model\ResourceModel\Product                                       $listingProductResource,
        \M2E\TikTokShop\Model\ResourceModel\Tag                                           $tagResource,
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory             $listingProductCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template                          $context,
        \Magento\Backend\Helper\Data                                                      $backendHelper,
        array                                                                             $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->tagResource = $tagResource;
        $this->relationCollectionFactory = $relationCollectionFactory;
        $this->listingResource = $listingResource;
        $this->listingProductResource = $listingProductResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ttsListingItemsByIssueGrid');

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('total_items');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/itemsByIssue/grid.css');

        return parent::_prepareLayout();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/tiktokshop_listing/itemsByIssue', ['_current' => true]);
    }

    protected function _prepareCollection()
    {
        $collection = $this->relationCollectionFactory->create();

        $collection->getSelect()->join(
            ['tag' => $this->tagResource->getMainTable()],
            'main_table.tag_id = tag.id'
        );

        $collection->join(
            ['lp' => $this->listingProductResource->getMainTable()],
            'main_table.listing_product_id = lp.id'
        );

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns([
            'total_items' => new \Magento\Framework\DB\Sql\Expression('COUNT(*)'),
            'tag_id' => 'tag.id',
            'text' => 'tag.text',
            'error_code' => 'tag.error_code',
        ]);

        $collection->getSelect()->where('tag.error_code != ?', \M2E\TikTokShop\Model\Tag::HAS_ERROR_ERROR_CODE);
        $collection->getSelect()->group('main_table.tag_id');

        $accountId = $this->getRequest()->getParam('account') ?
            (int)$this->getRequest()->getParam('account') :
            null;
        $shopId = $this->getRequest()->getParam('shop') ?
            (int)$this->getRequest()->getParam('shop') :
            null;

        if ($accountId !== null || $shopId !== null) {
            $collection->join(
                ['l' => $this->listingResource->getMainTable()],
                'lp.listing_id = l.id'
            );
        }

        if ($accountId !== null) {
            $collection->getSelect()->where('l.account_id = ?', $accountId);
        }

        if ($shopId !== null) {
            $collection->getSelect()->where(sprintf('l.%s = ?', ListingResource::COLUMN_SHOP_ID), $shopId);
        }

        $allItemsSubSelect = $this->getAllItemsSubSelect($accountId, $shopId);

        $collection->getSelect()->columns([
            'impact_rate' => new \Magento\Framework\DB\Sql\Expression(
                'COUNT(*) * 100 /(' . $allItemsSubSelect . ')'
            ),
        ]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    private function getAllItemsSubSelect(?int $accountId, ?int $shopId): \Magento\Framework\DB\Select
    {
        $collection = $this->listingProductCollectionFactory->create();

        if ($accountId !== null || $shopId !== null) {
            $collection->joinInner(
                ['l' => $this->listingResource->getMainTable()],
                'l.id=main_table.listing_id',
                []
            );
        }

        if ($accountId !== null) {
            $collection->getSelect()->where('l.account_id = ?', $accountId);
        }

        if ($shopId !== null) {
            $collection->getSelect()->where(sprintf('l.%s = ?', ListingResource::COLUMN_SHOP_ID), $shopId);
        }

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns('COUNT(*)');

        return $collection->getSelect();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'error_code',
            [
                'header' => __('Error Code'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'error_code',
                'sortable' => false,
                'filter_index' => 'tag.nick',
                'filter_condition_callback' => [$this, 'callbackFilterErrorCode'],
                'column_css_class' => 'listing-by-issue-grid-column-setting',
            ]
        );

        $this->addColumn(
            'issue',
            [
                'header' => __('Issue'),
                'align' => 'left',
                'index' => 'text',
                'type' => 'text',
                'sortable' => false,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'total_items',
            [
                'header' => __('Affected Items'),
                'align' => 'right',
                'type' => 'number',
                'index' => 'total_items',
                'filter' => false,
                'frame_callback' => [$this, 'callbackTotalItems'],
                'column_css_class' => 'listing-by-issue-grid-column-setting',
            ]
        );

        $this->addColumn(
            'impact_rate',
            [
                'header' => __('Impact Rate'),
                'align' => 'right',
                'type' => 'number',
                'index' => 'impact_rate',
                'filter' => false,
                'frame_callback' => [$this, 'callbackImpactRate'],
                'column_css_class' => 'listing-by-issue-grid-column-setting',
            ]
        );

        return parent::_prepareColumns();
    }

    protected function callbackFilterErrorCode(
        \M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation\Collection $collection,
        \M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite $column
    ): void {
        if ($errorCode = $column->getFilter()->getValue()) {
            $collection->getSelect()->where('tag.error_code LIKE ?', '%' . $errorCode . '%');
        }
    }

    public function callbackTotalItems(
        string $value,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Relation $row,
        \M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite $column,
        bool $isExport
    ): string {
        $url = $this->getUrl(
            '*/tiktokshop_listing/allItems',
            [TagSwitcher::TAG_ID_REQUEST_PARAM_KEY => $row->getData('tag_id')]
        );

        return sprintf("<a href='%s'>%s</a>", $url, $row->getData('total_items'));
    }

    public function callbackImpactRate(
        ?string $value,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Relation $row,
        Rewrite $column,
        bool $isExport
    ): string {
        return round((float)$value, 1) . '%';
    }

    /**
     * @param \M2E\TikTokShop\Model\Tag\ListingProduct\Relation $item
     *
     * @return false
     */
    public function getRowUrl($item)
    {
        return false;
    }
}
