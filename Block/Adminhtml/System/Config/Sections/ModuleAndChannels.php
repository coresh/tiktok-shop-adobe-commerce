<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Sections;

class ModuleAndChannels extends \M2E\TikTokShop\Block\Adminhtml\System\Config\Sections
{
    private \M2E\TikTokShop\Helper\Module $moduleHelper;
    private \M2E\TikTokShop\Model\Cron\Config $cronConfig;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        \M2E\TikTokShop\Model\Cron\Config $cronConfig,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->cronConfig = $cronConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->addField(
            'module_and_channels_help',
            self::HELP_BLOCK,
            [
                'no_collapse' => true,
                'no_hide' => true,
                'content' => __(
                    '<p>Here you can manage the Module and Automatic Synchronization running,' .
                    'enable Channels you want to sell on. Read the ' .
                    '<a href="%url" target="_blank">article</a> for more details.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/m2e-tiktok-shop-global-settings'],
                ),
            ]
        );

        $fieldSet = $form->addFieldset(
            'configuration_settings_module',
            [
                'legend' => __('Module'),
                'collapsable' => false,
            ]
        );

        $isCronEnabled = (int)$this->cronConfig->isEnabled();
        $isModuleEnabled = (int)!$this->moduleHelper->isDisabled();

        if ($isModuleEnabled) {
            $fieldSet->addField(
                'cron_mode_field',
                self::STATE_CONTROL_BUTTON,
                [
                    'name' => 'groups[module_mode][fields][cron_mode_field][value]',
                    'label' => __('Automatic Synchronization'),
                    'content' => $isCronEnabled ? 'Disable' : 'Enable',
                    'value' => $isCronEnabled,
                    'tooltip' => __(
                        'Inventory and Order synchronization stops. The Module interface remains available.'
                    ),
                    'onclick' => 'toggleCronStatus()',
                ]
            );
        }

        $fieldSet->addField(
            'module_mode_field',
            self::STATE_CONTROL_BUTTON,
            [
                'name' => 'groups[module_mode][fields][module_mode_field][value]',
                'label' => __('Module Interface and Automatic Synchronization'),
                'content' => $isModuleEnabled ? 'Disable' : 'Enable',
                'value' => $isModuleEnabled,
                'tooltip' => __(
                    'Inventory and Order synchronization stops. The Module interface becomes unavailable.'
                ),
                'onclick' => 'toggleTikTokShopModuleStatus()',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml(): string
    {
        $popup = $this->getLayout()
                      ->createBlock(\M2E\TikTokShop\Block\Adminhtml\System\Config\Popup\ModuleControlPopup::class);

        return $popup->toHtml() . parent::_toHtml();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->js->add(
            <<<JS
toggleStatus = function (objectId) {
    var field = $(objectId);
    field.value = (field.value === '0') ? '1' : '0';
    $('save').click();
}
toggleCronStatus = function () {
    toggleStatus('cron_mode_field');
}
JS
        );
    }
}
