<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Settings;

class Tabs extends \M2E\TikTokShop\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_MAIN = 'main';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('configuration_settings_tabs');
        $this->setDestElementId('tabs_container');
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('settings.css');

        // ---------------------------------------

        $tab = [
            'label' => __('Main'),
            'title' => __('Main'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Settings\Tabs\Main::class)
                              ->toHtml(),
        ];

        $this->addTab(self::TAB_ID_MAIN, $tab);

        // ---------------------------------------

        $this->setActiveTab(self::TAB_ID_MAIN);

        return parent::_prepareLayout();
    }

    public function getActiveTab()
    {
        return $this->_tabs[self::TAB_ID_MAIN] ?? null;
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop/getGlobalMessages'),
            'getGlobalMessages'
        );

        $this->jsTranslator->addTranslations([
            'Settings saved' => __('Settings saved'),
            'Error' => __('Error'),
        ]);
        $this->js->addRequireJs(
            [
                's' => 'TikTokShop/Settings',
            ],
            <<<JS

        window.SettingsObj = new Settings();
JS
        );

        return parent::_beforeToHtml();
    }
}
