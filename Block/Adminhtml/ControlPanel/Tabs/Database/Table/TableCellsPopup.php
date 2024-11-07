<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database\Table;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class TableCellsPopup extends AbstractBlock
{
    public const MODE_CREATE = 'create';
    public const MODE_UPDATE = 'update';

    private string $tableName;
    private string $mode = self::MODE_UPDATE;
    private array $rowsIds = [];

    public \M2E\TikTokShop\Model\ControlPanel\Database\TableModel $tableModel;
    private \M2E\TikTokShop\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        array $data = []
    ) {
        $this->databaseTableFactory = $databaseTableFactory;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabaseTableCellsPopup');
        // ---------------------------------------

        $this->setTemplate('control_panel/tabs/database/table_cells_popup.phtml');

        $this->init();
    }

    private function init()
    {
        $this->tableName = $this->getRequest()->getParam('table');
        $this->mode = $this->getRequest()->getParam('mode');
        $this->rowsIds = explode(',', $this->getRequest()->getParam('ids'));

        $model = $this->databaseTableFactory->create($this->tableName);

        $this->tableModel = $model;
    }

    public function isUpdateCellsMode(): bool
    {
        return $this->mode === self::MODE_UPDATE;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getIds(): array
    {
        return $this->rowsIds;
    }
}
