<?php

namespace M2E\TikTokShop\Plugin\Config\Magento\Config\Model\Config\Structure;

use M2E\TikTokShop\Helper\View\Configuration;

class Data extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    /** @var \M2E\TikTokShop\Helper\Module */
    private $moduleHelper;
    /** @var \M2E\TikTokShop\Helper\Module\Maintenance */
    private $moduleMaintenanceHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        \M2E\TikTokShop\Helper\Module\Maintenance $moduleMaintenanceHelper
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->moduleMaintenanceHelper = $moduleMaintenanceHelper;
    }

    protected function canExecute(): bool
    {
        return true;
    }

    public function aroundGet($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('get', $interceptor, $callback, $arguments);
    }

    /**
     * @param mixed $interceptor
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    protected function processGet($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);

        if (
            $this->moduleMaintenanceHelper->isEnabled()
            || !$this->moduleHelper->areImportantTablesExist()
        ) {
            unset($result['sections'][Configuration::MODULE_AND_CHANNELS_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::INTERFACE_AND_MAGENTO_INVENTORY_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::LOGS_CLEARING_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::EXTENSION_KEY_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::MIGRATION_SECTION_COMPONENT]);

            unset($result['sections']['payment']['children']['tiktokshoppayment']);
            unset($result['sections']['carriers']['children']['tiktokshopshipping']);
        } elseif ($this->moduleHelper->isDisabled()) {
            unset($result['sections'][Configuration::INTERFACE_AND_MAGENTO_INVENTORY_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::LOGS_CLEARING_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::EXTENSION_KEY_SECTION_COMPONENT]);
            unset($result['sections'][Configuration::MIGRATION_SECTION_COMPONENT]);

            unset($result['sections']['payment']['children']['tiktokshoppayment']);
            unset($result['sections']['carriers']['children']['tiktokshopshipping']);
        }

        return $result;
    }
}
