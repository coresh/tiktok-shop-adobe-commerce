<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Mapping;

class Tabs extends \M2E\TikTokShop\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_MAPPING_ATTRIBUTES = 'mapping';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('configuration_mapping_tabs');
        $this->setDestElementId('tabs_container');
    }

    protected function _prepareLayout()
    {
        $tab = [
            'label' => __('Attribute Mapping'),
            'title' => __('Attribute Mapping'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Mapping\Tabs\AttributeMapping::class)
                              ->toHtml(),
        ];

        $this->addTab(self::TAB_ID_MAPPING_ATTRIBUTES, $tab);

        // ---------------------------------------

        $this->setActiveTab(self::TAB_ID_MAPPING_ATTRIBUTES);

        return parent::_prepareLayout();
    }

    public function getActiveTab()
    {
        return $this->_tabs[self::TAB_ID_MAPPING_ATTRIBUTES] ?? null;
    }

    protected function _beforeToHtml()
    {
        $this->js->addRequireJs(
            [
                's' => 'TikTokShop/Mapping',
            ],
            <<<JS

        window.MappingObj = new Mapping();
JS
        );

        return parent::_beforeToHtml();
    }
}
