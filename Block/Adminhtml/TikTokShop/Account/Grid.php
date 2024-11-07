<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Account\Grid
{
    private \M2E\TikTokShop\Model\ResourceModel\Account\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($viewHelper, $context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->jsTranslator->addTranslations(
            [
                'Be attentive! By Deleting Account you delete all information on it from M2E TikTok Shop Connect Server. '
                . 'This will cause inappropriate work of all Accounts\' copies.' => __(
                    'Be attentive! By Deleting Account you delete all information on it from M2E TikTok Shop Connect Server. '
                    . 'This will cause inappropriate work of all Accounts\' copies.'
                ),
                'No Customer entry is found for specified ID.' => __(
                    'No Customer entry is found for specified ID.'
                ),
                'If Yes is chosen, you must select at least one Attribute for Product Linking.' => __(
                    'If Yes is chosen, you must select at least one Attribute for Product Linking.'
                ),
                'You should create at least one Response Template.' => __(
                    'You should create at least one Response Template.'
                ),
            ]
        );

        $this->jsUrl->addUrls([
            '*/tiktokshop_account/delete' => $this->getUrl('*/tiktokshop_account/delete/'),
        ]);

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Account'
    ], function(){
        window.TikTokShopAccountObj = new TikTokShopAccount();
    });
JS
        );
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'id',
            'filter_index' => 'main_table.id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title / Info'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => true,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        return parent::_prepareColumns();
    }

    public function callbackColumnTitle($value, $row, $column, $isExport): string
    {
        /** @var \M2E\TikTokShop\Model\Account $row */
        $sellerNamelabel =  __('Seller Name');
        $sellerName = $row->getSellerName();

        return <<<HTML
        <div>
            {$value}<br/>
            <span style="font-weight: bold">{$sellerNamelabel}</span>:
            <span style="color: #505050">{$sellerName}</span>
            <br/>
            <br/>
        </div>
HTML;
    }

    public function callbackColumnActions($value, $row, $column, $isExport): string
    {
        $delete = __('Delete');

        return <<<HTML
<div>
    <a class="action-default" href="javascript:" onclick="TikTokShopAccountObj.deleteClick('{$row->getId()}')">
        {$delete}
    </a>
</div>
HTML;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.title LIKE ?', '%' . $value . '%');
    }

    public function getRowUrl($item): string
    {
        return $this->getUrl('*/*/edit', ['id' => $item->getData('id')]);
    }
}
