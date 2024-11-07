<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database;

use M2E\TikTokShop\Model\ResourceModel\Collection\Custom;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    /* The table is excluded because it uses a composite primary key that causes magenta to fail */
    private array $excludedTables = [];

    private \M2E\TikTokShop\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $databaseHelper;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Module\Database\Structure $databaseHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->customCollectionFactory = $customCollectionFactory;
        $this->databaseHelper = $databaseHelper;
        $this->magentoHelper = $magentoHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->_isExport = true;

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabaseGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('table_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultLimit(50);
        // ---------------------------------------
    }

    /**
     * @throws \Magento\Framework\Exception\ValidatorException
     * @throws \Zend_Db_Statement_Exception
     * @throws \ReflectionException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $structureHelper = $this->databaseHelper;

        $tablesList = $this->magentoHelper->getMySqlTables();
        $databaseTablePrefix = $this->magentoHelper->getDatabaseTablesPrefix();

        $tablesList = array_map(
            fn (string $tableName) => str_replace($databaseTablePrefix, '', $tableName),
            $tablesList
        );

        $tablesList = array_unique(array_merge($tablesList, $structureHelper->getModuleTables()));

        $collection = $this->customCollectionFactory->create();
        foreach ($tablesList as $tableName) {
            if (
                !$structureHelper->isModuleTable($tableName)
                || in_array($tableName, $this->excludedTables, true)
            ) {
                continue;
            }

            $tableRow = [
                'table_name' => $tableName,
                'is_exist' => $structureHelper->isTableExists($tableName),
                'records' => 0,
                'size' => 0,
                'model' => $structureHelper->getTableModel($tableName),
            ];

            if ($tableRow['is_exist']) {
                $tableRow['size'] = $structureHelper->getDataLength($tableName);
                $tableRow['records'] = $structureHelper->getCountOfRecords($tableName);
            }

            $collection->addItem(new \Magento\Framework\DataObject($tableRow));
        }

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('table_name', [
            'header' => __('Table Name'),
            'align' => 'left',
            'index' => 'table_name',
            'filter_index' => 'table_name',
            'frame_callback' => [$this, 'callbackColumnTableName'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('records', [
            'header' => __('Records'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'records',
            'type' => 'number',
            'filter' => false,
        ]);

        $this->addColumn('size', [
            'header' => __('Size (Mb)'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'size',
            'filter' => false,
        ]);

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTableName($value, $row, $column, $isExport)
    {
        if (!$row->getData('is_exist')) {
            return sprintf(
                '<p style="color: red; font-weight: bold;">%s [table is not exists]</p>',
                $value
            );
        }

        if (!$row->getData('model')) {
            return sprintf('<p style="color: #878787;">%s</p>', $value);
        }

        return "<p>$value</p>";
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('table_name');
        $this->getMassactionBlock()->setFormFieldName('tables');
        $this->getMassactionBlock()->setUseSelectAll(false);
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/controlPanel/databaseTab', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        if (!$item->getData('is_exist') || !$item->getData('model')) {
            return false;
        }

        return $this->getUrl(
            '*/controlPanel_database/manageTable',
            ['table' => $item->getData('table_name')]
        );
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection() && $column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
        }

        return $this;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->addFilter(
            'table_name',
            $value,
            \M2E\TikTokShop\Model\ResourceModel\Collection\Custom::CONDITION_LIKE
        );
    }

    protected function callbackFilterMatch($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex()
            : $column->getIndex();

        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        $this->getCollection()->addFilter(
            $field,
            $value,
            \M2E\TikTokShop\Model\ResourceModel\Collection\Custom::CONDITION_MATCH
        );
    }
}
