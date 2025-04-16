<?php

namespace M2E\TikTokShop\Plugin\Menu\Magento\Backend\Model\Menu;

use M2E\TikTokShop\Helper\Module;
use M2E\TikTokShop\Helper\View\TikTokShop;
use M2E\TikTokShop\Helper\Module\Maintenance as Maintenance;

class Config extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    private const MENU_STATE_REGISTRY_KEY = '/menu/state/';
    private const MAINTENANCE_MENU_STATE_CACHE_KEY = 'maintenance_menu_state';

    private \Magento\Backend\Model\Menu\Item\Factory $itemFactory;
    private \M2E\TikTokShop\Model\Registry\Manager $registry;

    protected bool $isProcessed = false;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registry,
        \Magento\Backend\Model\Menu\Item\Factory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->registry = $registry;
    }

    protected function canExecute(): bool
    {
        /** @var \M2E\TikTokShop\Helper\Module $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module::class
        );

        return $helper->areImportantTablesExist();
    }

    public function aroundGetMenu(\Magento\Backend\Model\Menu\Config $interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getMenu', $interceptor, $callback, $arguments);
    }

    protected function processGetMenu(
        \Magento\Backend\Model\Menu\Config $interceptor,
        \Closure $callback,
        array $arguments
    ) {
        /** @var \Magento\Backend\Model\Menu $menuModel */
        $menuModel = $callback(...$arguments);

        if ($this->isProcessed) {
            return $menuModel;
        }

        $this->isProcessed = true;

        // ---------------------------------------

        /** @var \M2E\TikTokShop\Helper\Data\Cache\Permanent $cachePermanentHelper */
        $cachePermanentHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Data\Cache\Permanent::class
        );

        $maintenanceMenuState = $cachePermanentHelper->getValue(
            self::MAINTENANCE_MENU_STATE_CACHE_KEY
        );

        /** @var \M2E\Core\Helper\Magento $helper */
        $magentoHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Core\Helper\Magento::class
        );

        /** @var \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper */
        $maintenanceHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module\Maintenance::class
        );

        if ($maintenanceHelper->isEnabled()) {
            if ($maintenanceMenuState === null) {
                $cachePermanentHelper->setValue(
                    self::MAINTENANCE_MENU_STATE_CACHE_KEY,
                    true
                );
                $magentoHelper->clearMenuCache();
            }
            $this->processMaintenance($menuModel);

            return $menuModel;
        }

        if ($maintenanceMenuState !== null) {
            $cachePermanentHelper->removeValue(
                self::MAINTENANCE_MENU_STATE_CACHE_KEY
            );
            $magentoHelper->clearMenuCache();
        }

        // ---------------------------------------

        $currentMenuState = $this->buildMenuStateData();
        $previousMenuState = $this->registry->getValueFromJson(self::MENU_STATE_REGISTRY_KEY);

        if ($previousMenuState != $currentMenuState) {
            $this->registry->setValue(self::MENU_STATE_REGISTRY_KEY, $currentMenuState);
            $magentoHelper->clearMenuCache();
        }

        // ---------------------------------------

        /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
        $moduleHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module::class
        );

        if ($moduleHelper->isDisabled()) {
            $this->processModuleDisable($menuModel);

            return $menuModel;
        }

        $this->processWizard($menuModel->get(TikTokShop::MENU_ROOT_NODE_NICK));

        return $menuModel;
    }

    private function processMaintenance(\Magento\Backend\Model\Menu $menuModel)
    {
        $menuModelItem = $menuModel->get(TikTokShop::MENU_ROOT_NODE_NICK);

        if ($menuModelItem !== null && $menuModelItem->isAllowed()) {
            $maintenanceMenuItemResource = TikTokShop::MENU_ROOT_NODE_NICK;
        }

        foreach ($menuModel as $menuIndex => $menuItem) {
            if ($menuItem->getId() == $maintenanceMenuItemResource) {
                $maintenanceMenuItem = $this->itemFactory->create([
                    'id' => Maintenance::MENU_ROOT_NODE_NICK,
                    'module' => Module::IDENTIFIER,
                    'title' => \M2E\TikTokShop\Helper\Module::getChannelTitle(),
                    'resource' => $maintenanceMenuItemResource,
                    'action' => 'm2e_tiktokshop/maintenance',
                ]);

                $menuModel->remove($maintenanceMenuItemResource);
                $menuModel->add($maintenanceMenuItem, null, $menuIndex);
                break;
            }
        }

        $this->processModuleDisable($menuModel);
    }

    private function processModuleDisable(\Magento\Backend\Model\Menu $menuModel)
    {
        $menuModel->remove(TikTokShop::MENU_ROOT_NODE_NICK);
    }

    private function processWizard(?\Magento\Backend\Model\Menu\Item $menu): void
    {
        if ($menu === null) {
            return;
        }

        /** @var \M2E\TikTokShop\Helper\Module\Wizard $wizard */
        $wizard = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module\Wizard::class
        );

        $activeBlocker = $wizard->getActiveBlockerWizard(TikTokShop::NICK);

        if ($activeBlocker === null) {
            return;
        }

        $menu->getChildren()->exchangeArray([]);

        $actionUrl = 'm2e_tiktokshop/wizard_' . $activeBlocker->getNick();
        $menu->setAction($actionUrl);
    }

    private function buildMenuStateData(): array
    {
        /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
        $moduleHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module::class
        );

        /** @var \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper */
        $wizardHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Module\Wizard::class
        );

        return [
            Module::IDENTIFIER => [
                $moduleHelper->isDisabled(),
            ],
            TikTokShop::MENU_ROOT_NODE_NICK => [
                $wizardHelper->getActiveBlockerWizard(TikTokShop::NICK) === null,
            ],
        ];
    }
}
