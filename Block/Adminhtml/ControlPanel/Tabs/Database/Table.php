<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer;

class Table extends AbstractContainer
{
    protected \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper;
    private string $tableName;

    public function __construct(
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        string $tableName
    ) {
        $this->controlPanelHelper = $controlPanelHelper;
        $this->tableName = $tableName;
        parent::__construct($context);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabaseTable');
        $this->_controller = 'adminhtml_controlPanel_tabs_database_table';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------

        $title = sprintf('Manage Table "%s"', $this->tableName);

        $this->pageConfig->getTitle()->prepend($title);
        $this->_headerText = $title;
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->controlPanelHelper->getPageDatabaseTabUrl();
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => "window.open('{$url}','_blank')",
            'class' => 'back',
        ]);
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/controlPanel_tools/magento', ['action' => 'clearMagentoCache']);
        $this->addButton('additional-actions', [
            'label' => __('Additional Actions'),
            'onclick' => '',
            'class' => 'action-secondary',
            'sort_order' => 100,
            'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\DropDown::class,
            'options' => [
                'clear-cache' => [
                    'label' => __('Flush Cache'),
                    'onclick' => "window.open('{$url}', '_blank');",
                ],
            ],
        ]);

        $this->addButton('add_row', [
            'label' => __('Append Row'),
            'onclick' => 'ControlPanelDatabaseGridObj.openTableCellsPopup(\'add\')',
            'class' => 'action-success',
            'sort_order' => 90,
        ]);
    }
}
