<?php

namespace M2E\TikTokShop\Plugin;

use M2E\TikTokShop\Model\Exception;

abstract class AbstractPlugin
{
    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    protected function execute($name, $interceptor, \Closure $callback, array $arguments = [])
    {
        if (!$this->canExecute()) {
            return empty($arguments)
                ? $callback()
                : call_user_func_array($callback, $arguments);
        }

        $processMethod = 'process' . ucfirst($name);
        if (!method_exists($this, $processMethod)) {
            throw new Exception("Method $processMethod doesn't exists");
        }

        return $this->{$processMethod}($interceptor, $callback, $arguments);
    }

    protected function canExecute(): bool
    {
        /** @var \M2E\Core\Helper\Magento $helper */
        $magentoHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Core\Helper\Magento::class
        );
        if ($magentoHelper->isInstalled() === false) {
            return false;
        }

        /** @var \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper */
        $maintenanceHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module\Maintenance::class
        );        if ($maintenanceHelper->isEnabled()) {
            return false;
        }

        /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
        $moduleHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module::class
        );
        if (!$moduleHelper->isReadyToWork()) {
            return false;
        }

        if ($moduleHelper->isDisabled()) {
            return false;
        }

        return true;
    }
}
