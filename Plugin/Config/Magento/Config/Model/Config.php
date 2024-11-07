<?php

namespace M2E\TikTokShop\Plugin\Config\Magento\Config\Model;

use M2E\TikTokShop\Helper\View\Configuration;

class Config extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    /** @var \Magento\Framework\App\RequestInterface */
    private $request;
    /** @var \M2E\TikTokShop\Helper\Module */
    private $moduleHelper;
    /** @var \M2E\TikTokShop\Helper\Module\Maintenance */
    private $moduleMaintenanceHelper;
    /** @var \M2E\TikTokShop\Helper\Module\Configuration */
    private $moduleConfigurationHelper;
    /** @var \M2E\TikTokShop\Model\Log\Clearing */
    private $logClearing;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        \M2E\TikTokShop\Helper\Module\Maintenance $moduleMaintenanceHelper,
        \M2E\TikTokShop\Helper\Module\Configuration $moduleConfigurationHelper,
        \M2E\TikTokShop\Model\Log\Clearing $logClearing,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);

        $this->request = $request;
        $this->moduleHelper = $moduleHelper;
        $this->moduleMaintenanceHelper = $moduleMaintenanceHelper;
        $this->moduleConfigurationHelper = $moduleConfigurationHelper;
        $this->logClearing = $logClearing;
    }

    /**
     * @return bool
     */
    protected function canExecute(): bool
    {
        if ($this->moduleMaintenanceHelper->isEnabled()) {
            return false;
        }

        return true;
    }

    public function aroundSave(\Magento\Config\Model\Config $interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('save', $interceptor, $callback, $arguments);
    }

    /**
     * @param \Magento\Config\Model\Config $interceptor
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return \Magento\Config\Model\Config|mixed
     */
    protected function processSave(\Magento\Config\Model\Config $interceptor, \Closure $callback, array $arguments)
    {
        $saveData = $this->request->getParams();

        $availableSections = [
            Configuration::MODULE_AND_CHANNELS_SECTION_COMPONENT,
            Configuration::INTERFACE_AND_MAGENTO_INVENTORY_SECTION_COMPONENT,
            Configuration::LOGS_CLEARING_SECTION_COMPONENT,
            Configuration::EXTENSION_KEY_SECTION_COMPONENT,
            Configuration::MIGRATION_SECTION_COMPONENT,
        ];

        if (
            !isset($saveData['section'])
            || !in_array($saveData['section'], $availableSections)
        ) {
            return $callback(...$arguments);
        }

        switch ($saveData['section']) {
            case Configuration::MODULE_AND_CHANNELS_SECTION_COMPONENT:
                $this->processModuleAndChannelsSection($saveData['groups']);
                break;
            case Configuration::INTERFACE_AND_MAGENTO_INVENTORY_SECTION_COMPONENT:
                $this->processInterfaceAndMagentoInventorySection($saveData['groups']);
                break;
            case Configuration::LOGS_CLEARING_SECTION_COMPONENT:
                $this->processLogsClearingSection($saveData['groups']);
                break;
        }

        return $interceptor;
    }

    /**
     * @param array $groups
     *
     * @return void
     */
    private function processModuleAndChannelsSection(array $groups): void
    {
        if (isset($groups['module_mode']['fields']['module_mode_field']['value'])) {
            $this->moduleHelper->getConfig()->setGroupValue(
                '/',
                'is_disabled',
                (int)!$groups['module_mode']['fields']['module_mode_field']['value']
            );
        }

        if (isset($groups['module_mode']['fields']['cron_mode_field']['value'])) {
            $this->moduleHelper->getConfig()->setGroupValue(
                '/cron/',
                'mode',
                (int)$groups['module_mode']['fields']['cron_mode_field']['value']
            );
        }
    }

    /**
     * @param array $groups
     *
     * @return void
     */
    private function processInterfaceAndMagentoInventorySection(array $groups): void
    {
        $fields = array_merge(
            $groups['interface']['fields'],
            $groups['quantity_and_price']['fields'],
        );

        foreach ($fields as $field => $value) {
            $fields[$field] = (int)$value['value'];
        }

        // allowed field names is checking in setConfigValues() method
        $this->moduleConfigurationHelper->setConfigValues($fields);
    }

    /**
     * @param array $groups
     *
     * @return void
     */
    private function processLogsClearingSection(array $groups): void
    {
        $this->logClearing->saveSettings(
            \M2E\TikTokShop\Model\Log\Clearing::LOG_LISTINGS,
            (bool)(int)$groups['listings_logs_and_events_clearing']['fields']['listings_log_mode_field']['value'],
            (int)$groups['listings_logs_and_events_clearing']['fields']['listings_log_days_field']['value']
        );
        $this->logClearing->saveSettings(
            \M2E\TikTokShop\Model\Log\Clearing::LOG_ORDERS,
            (bool)(int)$groups['orders_logs_and_events_clearing']['fields']['orders_log_mode_field']['value'],
            90
        );
        $this->logClearing->saveSettings(
            \M2E\TikTokShop\Model\Log\Clearing::LOG_SYNCHRONIZATIONS,
            (bool)(int)$groups['sync_logs_and_events_clearing']['fields']['sync_log_mode_field']['value'],
            (int)$groups['sync_logs_and_events_clearing']['fields']['sync_log_days_field']['value']
        );
    }
}
