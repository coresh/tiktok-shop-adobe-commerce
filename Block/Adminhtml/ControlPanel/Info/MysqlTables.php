<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Info;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;
use M2E\TikTokShop\Helper\Module\Database\Structure;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Info\MysqlTables
 * @method getTablesList()
 */
class MysqlTables extends AbstractBlock
{
    /** @var \M2E\TikTokShop\Helper\Module\Database\Structure */
    private $databaseHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Helper\Module\Database\Structure $databaseHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->databaseHelper = $databaseHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInfoMysqlTables');
        $this->setTemplate('control_panel/info/mysqlTables.phtml');
    }

    //########################################

    public function getTablesInfo()
    {
        $helper = $this->databaseHelper;
        $tablesInfo = [];

        foreach ($this->getTablesList() as $category => $tables) {
            foreach ($tables as $tableName) {
                $tablesInfo[$category][$tableName] = [
                    'count' => 0,
                    'url' => '#',
                ];

                if (!$helper->isTableReady($tableName)) {
                    continue;
                }

                $tablesInfo[$category][$tableName]['count'] = $helper->getCountOfRecords($tableName);
                $tablesInfo[$category][$tableName]['url'] = $this->getUrl(
                    '*/controlPanel_database/manageTable',
                    ['table' => $tableName]
                );
            }
        }

        return $tablesInfo;
    }

    //########################################
}
