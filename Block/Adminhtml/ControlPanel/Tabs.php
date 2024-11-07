<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel;

use M2E\TikTokShop\Block\Adminhtml\Magento\Tabs\AbstractHorizontalTabs;
use M2E\TikTokShop\Helper\View\ControlPanel as HelperControlPanel;

class Tabs extends AbstractHorizontalTabs
{
    public function _construct(): void
    {
        parent::_construct();
        $this->setDestElementId('control_panel_tab_container');
    }

    protected function _prepareLayout()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        $allowedTabs = [
            HelperControlPanel::TAB_OVERVIEW,
            HelperControlPanel::TAB_INSPECTION,
            HelperControlPanel::TAB_DATABASE,
            HelperControlPanel::TAB_TOOLS_MODULE,
            HelperControlPanel::TAB_CRON,
            HelperControlPanel::TAB_DEBUG,
        ];

        if (!in_array($activeTab, $allowedTabs)) {
            $activeTab = HelperControlPanel::TAB_OVERVIEW;
        }

        // ---------------------------------------
        $this->addTab(HelperControlPanel::TAB_OVERVIEW, [
            'label' => __('Overview'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Overview::class)
                              ->toHtml(),
        ]);
        // ---------------------------------------
        $params = ['label' => __('Inspection')];
        if ($activeTab === HelperControlPanel::TAB_INSPECTION) {
            $params['content'] = $this->getLayout()
                                      ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Inspection::class)
                                      ->toHtml();
        } else {
            $params['class'] = 'ajax';
            $params['url'] = $this->getUrl('*/controlPanel/InspectionTab');
        }
        $this->addTab(HelperControlPanel::TAB_INSPECTION, $params);

        // ---------------------------------------
        $params = ['label' => __('Database')];
        if ($activeTab === HelperControlPanel::TAB_DATABASE) {
            $params['content'] = $this->getLayout()
                                      ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database::class)
                                      ->toHtml();
        } else {
            $params['class'] = 'ajax';
            $params['url'] = $this->getUrl('*/controlPanel/databaseTab');
        }
        $this->addTab(HelperControlPanel::TAB_DATABASE, $params);
        // ---------------------------------------

        $this->addTab(HelperControlPanel::TAB_TOOLS_MODULE, [
            'label' => __('Module Tools'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\ToolsModule::class)
                              ->toHtml(),
        ]);

        $this->addTab(HelperControlPanel::TAB_CRON, [
            'label' => __('Cron'),
            'content' => $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Cron::class)
                              ->toHtml(),
        ]);

        $this->addTab(HelperControlPanel::TAB_DEBUG, [
            'label' => __('Debug'),
            'content' => $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Debug::class)
                              ->toHtml(),
        ]);

        $this->setActiveTab($activeTab);

        return parent::_prepareLayout();
    }

    public function _toHtml()
    {
        $this->js->add(
            <<<JS
function SetupManagementActionHandler() {

    this.askAdditionalParametersForAction = function(promptString, url, placeHolder)
    {
        var result = prompt(promptString);

        if (result == null) {
            return false;
        }

        url = url.replace(encodeURIComponent('#') + placeHolder + encodeURIComponent('#'), result);
        document.location = url;
    }
}

window.setupManagementActionHandler = new SetupManagementActionHandler();
JS
        );

        return parent::_toHtml() . '<div id="control_panel_tab_container"></div>';
    }
}
