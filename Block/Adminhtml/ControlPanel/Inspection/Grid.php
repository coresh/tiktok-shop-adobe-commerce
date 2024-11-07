<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid as WidgetAbstractGrid;
use M2E\TikTokShop\Model\ResourceModel\Collection\Custom;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use M2E\TikTokShop\Model\ControlPanel\Inspection\Repository;

class Grid extends WidgetAbstractGrid
{
    public const NOT_SUCCESS_FILTER = 'not-success';

    /** @var \M2E\TikTokShop\Model\ResourceModel\Collection\CustomFactory */
    protected $customCollectionFactory;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var \M2E\TikTokShop\Model\ControlPanel\Inspection\Repository $repository */
    private $repository;

    //########################################

    public function __construct(
        Repository $repository,
        \M2E\TikTokShop\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ObjectManagerInterface $objectManager
    ) {
        $this->customCollectionFactory = $customCollectionFactory;
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        parent::__construct($context, $backendHelper);

        $this->setId('controlPanelInspectionsGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = $this->customCollectionFactory->create();

        foreach ($this->repository->getDefinitions() as $definition) {
            $row = [
                'id' => $definition->getNick(),
                'title' => $definition->getTitle(),
                'description' => $definition->getDescription(),
                'group' => $definition->getGroup(),
            ];
            $collection->addItem(new DataObject($row));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'align' => 'left',
                'type' => 'text',
                'width' => '20%',
                'index' => 'title',
                'filter_index' => 'title',
                'filter_condition_callback' => [$this, 'callbackFilterLike'],
                'frame_callback' => [$this, 'callbackColumnTitle'],
            ]
        );

        $this->addColumn(
            'details',
            [
                'header' => __('Details'),
                'align' => 'left',
                'type' => 'text',
                'width' => '40%',
                'column_css_class' => 'details',
                'filter_index' => false,
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Actions'),
                'align' => 'left',
                'width' => '150px',
                'type' => 'action',
                'index' => 'actions',
                'filter' => false,
                'sortable' => false,
                'getter' => 'getId',
                'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
                'actions' => [
                    'checkAction' => [
                        'caption' => __('Check'),
                        'field' => 'id',
                        'onclick_action' => 'ControlPanelInspectionObj.checkAction',
                    ],
                ],
            ]
        );

        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'text',
                'index' => 'id',
                'column_css_class' => 'no-display id',
                'header_css_class' => 'no-display',
            ]
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->addItem(
            'checkAll',
            [
                'label' => __('Run'),
                'url' => '',
            ]
        );

        return parent::_prepareMassaction();
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();

        if ($field === 'id') {
            return $this;
        }

        return parent::_addColumnFilterToCollection($column);
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/controlPanel/InspectionTab', ['_current' => true]);
    }

    //########################################

    protected function callbackFilterLike($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        $this->getCollection()->addFilter($field, $value, Custom::CONDITION_LIKE);
    }

    protected function callbackFilterMatch($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        if ($value == self::NOT_SUCCESS_FILTER) {
            $field = 'need_attention';
            $value = '1';
        }

        $this->getCollection()->addFilter($field, $value, Custom::CONDITION_LIKE);
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = <<<HTML
<span style="color: grey;">[{$row->getData('group')}]</span> {$value}
HTML;

        if (!$row->getData('description')) {
            return $value;
        }

        return <<<HTML
<style>
    .admin__field-tooltip .admin__field-tooltip-content {
        bottom: 5rem;
    }
</style>
{$value}
<div class="TikTokShop-field-tooltip-to-right admin__field-tooltip">
    <a class="admin__field-tooltip-action"  style="bottom:8px;"></a>
    <div class="admin__field-tooltip-content">
           {$row->getData('description')}
    </div>
</div>
HTML;
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $urls = [
            'checkInspection' => $this->getUrl('*/controlPanel_inspection/checkInspection'),
        ];

        $this->jsUrl->addUrls($urls);

        // Set ids to be able to use option "Select All"
        $ids = [];

        foreach ($this->repository->getDefinitions() as $definition) {
            $ids[] = $definition->getNick();
        }

        $allIdsStr = implode(",", $ids);

        $this->js->addOnReadyJs(
            <<<JS
require(['domReady', 'TikTokShop/ControlPanel/Inspection'], function() {
    window.ControlPanelInspectionObj = new ControlPanelInspection('{$this->getId()}');
    window.ControlPanelInspectionObj.afterInitPage();
    window.ControlPanelInspectionObj.getGridMassActionObj().setGridIds('{$allIdsStr}');
});
JS
        );
    }
}
