<?php

namespace M2E\TikTokShop\Block\Adminhtml\Synchronization\Log;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Log\AbstractGrid
{
    private array $actionsTitles = [];
    private \M2E\TikTokShop\Model\ResourceModel\Synchronization\Log\CollectionFactory $syncLogCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Synchronization\Log\CollectionFactory $syncLogCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->syncLogCollectionFactory = $syncLogCollectionFactory;
        parent::__construct(
            $resourceConnection,
            $viewHelper,
            $context,
            $backendHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        $task = $this->getRequest()->getParam('task', '');

        $this->setId('synchronizationLogGrid' . $task . 'TikTokShop');

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $filters = [];
        if ($task !== null) {
            $filters['task'] = $task;
        }

        $this->setDefaultFilter($filters);

        $this->actionsTitles = \M2E\TikTokShop\Helper\Module\Log::getActionsTitlesByClass(
            \M2E\TikTokShop\Model\Synchronization\Log::class,
        );
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/synchronization_log/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _getLogTypeList(): array
    {
        return [
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_WARNING => (string)__('Warning'),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR => (string)__('Error'),
            \M2E\TikTokShop\Model\Synchronization\Log::TYPE_FATAL_ERROR => (string)__('Fatal Error'),
        ];
    }

    protected function _prepareCollection()
    {
        $collection = $this->syncLogCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date',
            [
                'header' => __('Date'),
                'align' => 'left',
                'type' => 'datetime',
                'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
                'filter_time' => true,
                'format' => \IntlDateFormatter::MEDIUM,
                'index' => 'create_date',
            ],
        );

        $this->addColumn(
            'task',
            [
                'header' => __('Task'),
                'align' => 'left',
                'type' => 'options',
                'index' => 'task',
                'sortable' => false,
                'filter_index' => 'task',
                'filter_condition_callback' => [$this, 'callbackFilterTask'],
                'option_groups' => $this->getActionTitles(),
                'options' => $this->actionsTitles,
            ],
        );

        $this->addColumn(
            'description',
            [
                'header' => __('Message'),
                'align' => 'left',
                'type' => 'text',
                'string_limit' => 350,
                'index' => 'description',
                'filter_index' => 'description',
                'frame_callback' => [$this, 'callbackColumnDescription'],
            ],
        );

        $this->addColumn(
            'detailed_description',
            [
                'header' => __('Detailed'),
                'align' => 'left',
                'type' => 'text',
                'string_limit' => 65000,
                'index' => 'detailed_description',
                'filter_index' => 'detailed_description',
                'frame_callback' => [$this, 'callbackColumnDescription'],
            ],
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'align' => 'right',
                'type' => 'options',
                'sortable' => false,
                'options' => $this->_getLogTypeList(),
                'frame_callback' => [$this, 'callbackColumnType'],
            ],
        );

        return parent::_prepareColumns();
    }

    /**
     * @param \M2E\TikTokShop\Model\ResourceModel\Synchronization\Log\Collection $collection
     * @param \M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite $column
     *
     * @return void
     */
    protected function callbackFilterTask($collection, $column): void
    {
        $taskCode = $column->getFilter()->getValue();

        if ($taskCode === null) {
            return;
        }

        if ((int)$taskCode === \M2E\TikTokShop\Model\Synchronization\Log::TASK_ALL) {
            return;
        }

        $collection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Synchronization\Log::COLUMN_TASK,
            ['eq' => (int)$taskCode]
        );
    }

    private function getActionTitles(): array
    {
        $titles = [];
        foreach ($this->actionsTitles as $value => $label) {
            $titles[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $titles;
    }
}
