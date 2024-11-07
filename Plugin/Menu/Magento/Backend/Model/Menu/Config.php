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
        \Magento\Backend\Model\Menu\Item\Factory $itemFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);
        $this->itemFactory = $itemFactory;
        $this->registry = $registry;
    }

    protected function canExecute(): bool
    {
        return $this->helperFactory->getObject('Module')->areImportantTablesExist();
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

        $maintenanceMenuState = $this->helperFactory->getObject('Data_Cache_Permanent')->getValue(
            self::MAINTENANCE_MENU_STATE_CACHE_KEY
        );

        if ($this->helperFactory->getObject('Module\Maintenance')->isEnabled()) {
            if ($maintenanceMenuState === null) {
                $this->helperFactory->getObject('Data_Cache_Permanent')->setValue(
                    self::MAINTENANCE_MENU_STATE_CACHE_KEY,
                    true
                );
                $this->helperFactory->getObject('Magento')->clearMenuCache();
            }
            $this->processMaintenance($menuModel);

            return $menuModel;
        }

        if ($maintenanceMenuState !== null) {
            $this->helperFactory->getObject('Data_Cache_Permanent')->removeValue(
                self::MAINTENANCE_MENU_STATE_CACHE_KEY
            );
            $this->helperFactory->getObject('Magento')->clearMenuCache();
        }

        // ---------------------------------------

        $currentMenuState = $this->buildMenuStateData();
        $previousMenuState = $this->registry->getValueFromJson(self::MENU_STATE_REGISTRY_KEY);

        if ($previousMenuState != $currentMenuState) {
            $this->registry->setValue(self::MENU_STATE_REGISTRY_KEY, $currentMenuState);
            $this->helperFactory->getObject('Magento')->clearMenuCache();
        }

        // ---------------------------------------

        if ($this->helperFactory->getObject('Module')->isDisabled()) {
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
                    'title' => 'TikTok Shop',
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
        $wizard = $this->helperFactory->getObject('Module\Wizard');
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
        return [
            Module::IDENTIFIER => [
                $this->helperFactory->getObject('Module')->isDisabled(),
            ],
            TikTokShop::MENU_ROOT_NODE_NICK => [
                $this->helperFactory->getObject('Module\Wizard')->getActiveBlockerWizard(TikTokShop::NICK) === null,
            ],
        ];
    }
}
