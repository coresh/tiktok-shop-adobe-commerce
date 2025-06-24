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
        $magentoHelper = $this->getService(\M2E\Core\Helper\Magento::class);
        if ($magentoHelper->isInstalled() === false) {
            return false;
        }

        /** @var \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper */
        $maintenanceHelper = $this->getService(\M2E\TikTokShop\Helper\Module\Maintenance::class);
        if ($maintenanceHelper->isEnabled()) {
            return false;
        }

        /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
        $moduleHelper = $this->getService(\M2E\TikTokShop\Helper\Module::class);
        if (!$moduleHelper->isReadyToWork()) {
            return false;
        }

        if ($moduleHelper->isDisabled()) {
            return false;
        }

        return true;
    }

    protected function isModuleTablesExist(): bool
    {
        /** @var \M2E\TikTokShop\Model\Module $module */
        $module = $this->getService(\M2E\TikTokShop\Model\Module::class);

        return $module->areImportantTablesExist();
    }

    private function getService(string $name): object
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get($name);
    }
}
