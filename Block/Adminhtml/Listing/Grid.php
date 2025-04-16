<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    protected $_groupedActions = [];
    protected $_actions = [];

    /** @var \M2E\TikTokShop\Helper\View */
    protected $viewHelper;

    /** @var \M2E\TikTokShop\Helper\Data */
    protected $dataHelper;
    private \M2E\Core\Helper\Url $urlHelper;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->viewHelper = $viewHelper;
        $this->urlHelper = $urlHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/grid.css');

        return parent::_prepareLayout();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------
        $currentView = $this->viewHelper->getCurrentView();

        // Set clear log action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'clear_logs',
            [
                'label' => __('Clear Log(s)'),
                'url' => $this->getUrl(
                    '*/listing/clearLog',
                    [
                        'back' => $this->urlHelper->makeBackUrlParam("*/{$currentView}_listing/index"),
                    ]
                ),
                'confirm' => __('Are you sure?'),
            ]
        );
        // ---------------------------------------

        // Set remove listings action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'delete_listings',
            [
                'label' => __('Delete Listing(s)'),
                'url' => $this->getUrl("*/{$currentView}_listing/delete"),
                'confirm' => __('Are you sure?'),
            ]
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'align' => 'left',
                'type' => 'number',
                'index' => 'id',
                'filter_index' => 'main_table.id',
            ]
        );

        $this->addColumn('title', [
            'header' => __('Title / Info'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => false,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('products_total_count', [
            'header' => __('Total Items'),
            'align' => 'right',
            'type' => 'number',
            'index' => 'products_total_count',
            'filter_index' => 't.products_total_count',
            'frame_callback' => [$this, 'callbackColumnProductsCount'],
        ]);

        $this->addColumn('products_active_count', [
            'header' => __('Active Items'),
            'align' => 'right',
            'type' => 'number',
            'index' => 'products_active_count',
            'filter_index' => 't.products_active_count',
            'frame_callback' => [$this, 'callbackColumnProductsCount'],
        ]);

        $this->addColumn('products_inactive_count', [
            'header' => __('Inactive Items'),
            'align' => 'right',
            'width' => 100,
            'type' => 'number',
            'index' => 'products_inactive_count',
            'filter_index' => 't.products_inactive_count',
            'frame_callback' => [$this, 'callbackColumnProductsCount'],
        ]);

        $this->setColumns();

        $this->addColumn('actions', [
            'header' => __('Actions'),
            'align' => 'left',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'getter' => 'getId',
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'group_order' => $this->getGroupOrder(),
            'actions' => $this->getColumnActionsItems(),
        ]);

        return parent::_prepareColumns();
    }

    protected function setColumns()
    {
        return null;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return $value;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        return null;
    }

    //########################################

    public function callbackColumnProductsCount($value, $row, $column, $isExport)
    {
        if (empty($value) || $value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    // ---------------------------------------

    protected function getGroupOrder()
    {
        return [
            'products_actions' => __('Products'),
            'edit_actions' => __('Edit Settings'),
            'other' => __('Other'),
        ];
    }

    protected function getColumnActionsItems()
    {
        return [];
    }

    //########################################
}
