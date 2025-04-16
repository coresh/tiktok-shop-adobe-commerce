<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Sections;

use M2E\TikTokShop\Model\Log\Clearing as LogClearing;

class LogsClearing extends \M2E\TikTokShop\Block\Adminhtml\System\Config\Sections
{
    /** @var array */
    private $modes;
    /** @var array */
    private $days;
    private \M2E\TikTokShop\Helper\Data $dataHelper;
    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->dataHelper = $dataHelper;
        $this->config = $config;
    }

    protected function _prepareForm()
    {
        $this->prepareFormData();

        $form = $this->_formFactory->create();

        $form->addField(
            'settings_tab_logs_clearing',
            self::HELP_BLOCK,
            [
                'content' => __('Set preferences for automatic clearing of Log data, then ' .
                    'click <strong>Save</strong>.You may clear the relevant logs manually by clicking ' .
                    '<strong>Clear All</strong>.'),
            ]
        );

        $fieldSet = $form->addFieldset(
            'magento_block_configuration_logs_clearing_listings',
            [
                'legend' => __(
                    '%extension_title Listings Logs & Events Clearing',
                    [
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                    ]
                ),
                'collapsable' => false,
            ]
        );

        $mode = $this->modes[LogClearing::LOG_LISTINGS] ?? 1;

        $tooltip = $this->getTooltipHtml(
            __('Enables automatic clearing of Log data. Can help reduce Database size.')
        );
        $logsType = LogClearing::LOG_LISTINGS;

        $fieldSet->addField(
            LogClearing::LOG_LISTINGS . '_log_mode',
            self::SELECT,
            [
                'name' => 'groups[listings_logs_and_events_clearing][fields][listings_log_mode_field][value]',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $mode,
                'onchange' => "LogClearingObj.changeModeLog('" . LogClearing::LOG_LISTINGS . "')",

                'field_extra_attributes' => 'id="' . LogClearing::LOG_LISTINGS . '_log_mode_container"',
                'after_element_html' => <<<HTML
$tooltip
<span id="{$logsType}_log_button_clear_all_container">
    {$this->getChildHtml('clear_all_' . LogClearing::LOG_LISTINGS)}
</span>
HTML
                ,
            ]
        );

        $fieldSet->addField(
            LogClearing::LOG_LISTINGS . '_log_days',
            self::TEXT,
            [
                'name' => 'groups[listings_logs_and_events_clearing][fields][listings_log_days_field][value]',
                'label' => __('Keep For (days)'),
                'title' => __('Keep For (days)'),
                'value' => $this->days[LogClearing::LOG_LISTINGS],
                'class' => 'TikTokShop-logs-clearing-interval',
                'required' => true,
                'tooltip' => __(
                    'Specify for how long you want to keep Log data before it is automatically cleared.'
                ),

                'field_extra_attributes' => 'id="' . LogClearing::LOG_LISTINGS . '_log_days_container"',
            ]
        );

        $fieldSet = $form->addFieldset(
            'magento_block_logs_configuration_clearing_orders',
            [
                'legend' => __('Orders Logs & Events Clearing'),
                'collapsable' => false,
            ]
        );

        $mode = $this->modes[LogClearing::LOG_ORDERS] ?? 1;
        $tooltip = $this->getTooltipHtml(
            __('Enables automatic clearing of Log data. Can help reduce Database size.')
        );
        $logsType = LogClearing::LOG_ORDERS;

        $fieldSet->addField(
            LogClearing::LOG_ORDERS . '_log_mode',
            self::SELECT,
            [
                'name' => 'groups[orders_logs_and_events_clearing][fields][orders_log_mode_field][value]',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $mode,
                'onchange' => "LogClearingObj.changeModeLog('" . LogClearing::LOG_ORDERS . "')",

                'field_extra_attributes' => 'id="' . LogClearing::LOG_ORDERS . '_log_mode_container"',
                'after_element_html' => <<<HTML
$tooltip
<span id="{$logsType}_log_button_clear_all_container">
    {$this->getChildHtml('clear_all_' . LogClearing::LOG_ORDERS)}
</span>
HTML
                ,
            ]
        );

        $fieldSet->addField(
            LogClearing::LOG_ORDERS . '_log_days',
            self::TEXT,
            [
                'name' => 'groups[orders_logs_and_events_clearing][fields][orders_log_days_field][value]',
                'label' => __('Keep For (days)'),
                'title' => __('Keep For (days)'),
                'value' => $this->days[LogClearing::LOG_ORDERS],
                'class' => 'TikTokShop-logs-clearing-interval',
                'required' => true,
                'tooltip' => __(
                    'Specify for how long you want to keep Log data before it is automatically cleared.'
                ),
                'disabled' => true,

                'field_extra_attributes' => 'id="' . LogClearing::LOG_ORDERS . '_log_days_container"',
            ]
        );

        $fieldSet = $form->addFieldset(
            'magento_block_configuration_logs_clearing_synch',
            [
                'legend' => __('Synchronization Logs & Events Clearing'),
                'collapsable' => false,
            ]
        );

        $mode = isset($this->modes[LogClearing::LOG_SYNCHRONIZATIONS])
            ? $this->modes[LogClearing::LOG_SYNCHRONIZATIONS] : 1;
        $tooltip = $this->getTooltipHtml(
            __('Enables automatic clearing of Log data. Can help reduce Database size.')
        );
        $logsType = LogClearing::LOG_SYNCHRONIZATIONS;

        $fieldSet->addField(
            LogClearing::LOG_SYNCHRONIZATIONS . '_log_mode',
            self::SELECT,
            [
                'name' => 'groups[sync_logs_and_events_clearing][fields][sync_log_mode_field][value]',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $mode,
                'onchange' => "LogClearingObj.changeModeLog('" . LogClearing::LOG_SYNCHRONIZATIONS . "')",

                'field_extra_attributes' => 'id="' . LogClearing::LOG_SYNCHRONIZATIONS . '_log_mode_container"',
                'after_element_html' => <<<HTML
$tooltip
<span id="{$logsType}_log_button_clear_all_container">
    {$this->getChildHtml('clear_all_' . LogClearing::LOG_SYNCHRONIZATIONS)}
</span>
HTML
                ,
            ]
        );

        $fieldSet->addField(
            LogClearing::LOG_SYNCHRONIZATIONS . '_log_days',
            self::TEXT,
            [
                'name' => 'groups[sync_logs_and_events_clearing][fields][sync_log_days_field][value]',
                'label' => __('Keep For (days)'),
                'title' => __('Keep For (days)'),
                'value' => $this->days[LogClearing::LOG_SYNCHRONIZATIONS],
                'class' => 'TikTokShop-logs-clearing-interval',
                'required' => true,
                'tooltip' => __(
                    'Specify for how long you want to keep Log data before it is automatically cleared.'
                ),

                'field_extra_attributes' => 'id="' . LogClearing::LOG_SYNCHRONIZATIONS . '_log_days_container"',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function prepareFormData(): void
    {
        $config = $this->config;
        $tasks = [
            LogClearing::LOG_LISTINGS,
            LogClearing::LOG_SYNCHRONIZATIONS,
            LogClearing::LOG_ORDERS,
        ];

        $modes = [];
        $days = [];

        foreach ($tasks as $task) {
            $modes[$task] = $config->getGroupValue('/logs/clearing/' . $task . '/', 'mode');
            $days[$task] = $config->getGroupValue('/logs/clearing/' . $task . '/', 'days');
        }

        $this->modes = $modes;
        $this->days = $days;
        // ---------------------------------------

        foreach ($tasks as $task) {
            if ($task === LogClearing::LOG_ORDERS) {
                continue;
            }

            // ---------------------------------------
            $data = [
                'label' => __('Clear All'),
                'onclick' => 'LogClearingObj.clearAllLog(\'' . $task . '\', this)',
                'class' => 'clear_all_' . $task . ' primary',
                'style' => 'margin-left: 6.5rem',
            ];
            $buttonBlock = $this->getLayout()
                                ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                                ->setData($data);
            $this->setChild('clear_all_' . $task, $buttonBlock);
            // ---------------------------------------
        }
    }

    protected function _prepareLayout(): void
    {
        parent::_prepareLayout();

        $this->jsTranslator->add(
            'logs_clearing_keep_for_days_validation_message',
            __('Please enter a valid value greater than 14 and less than 90 days.')
        );

        $this->jsUrl->add($this->getUrl('m2e_tiktokshop/settings_logsClearing/save'), self::SECTION_ID_LOGS_CLEARING);
        $this->jsUrl->add($this->getUrl('m2e_tiktokshop/settings_logsClearing/save'), 'formSubmit');

        $logData = [
            LogClearing::LOG_LISTINGS,
            LogClearing::LOG_SYNCHRONIZATIONS,
            LogClearing::LOG_ORDERS,
        ];

        $this->js->addRequireJs(
            [
                'sl' => 'TikTokShop/Settings/LogClearing',
            ],
            <<<JS
window.LogClearingObj = new SettingsLogClearing();
LogClearingObj.changeModeLog('$logData[0]');
LogClearingObj.changeModeLog('$logData[1]');
LogClearingObj.changeModeLog('$logData[2]');
JS
        );
    }
}
