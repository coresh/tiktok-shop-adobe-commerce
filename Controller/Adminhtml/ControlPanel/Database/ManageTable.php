<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class ManageTable extends AbstractTable
{
    protected \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        \M2E\TikTokShop\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\TikTokShop\Model\Module $module
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module);
        $this->controlPanelHelper = $controlPanelHelper;
    }

    public function execute()
    {
        $this->init();
        $table = $this->getRequest()->getParam('table');

        if ($table === null) {
            return $this->_redirect($this->controlPanelHelper->getPageDatabaseTabUrl());
        }

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database\Table::class,
                '',
                ['tableName' => $table],
            ),
        );

        return $this->getResultPage();
    }
}
