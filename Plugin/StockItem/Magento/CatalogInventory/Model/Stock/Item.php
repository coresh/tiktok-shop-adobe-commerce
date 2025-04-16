<?php

namespace M2E\TikTokShop\Plugin\StockItem\Magento\CatalogInventory\Model\Stock;

class Item extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    /** @var \Magento\Framework\Event\ManagerInterface */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    //########################################

    public function canExecute(): bool
    {
        if (!parent::canExecute()) {
            return false;
        }

        /** @var \M2E\Core\Helper\Magento $helper */
        $magentoHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Core\Helper\Magento::class
        );

        return version_compare($magentoHelper->getVersion(), '2.2.0', '<');
    }

    //########################################

    public function aroundBeforeSave($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('beforeSave', $interceptor, $callback, $arguments);
    }

    // ---------------------------------------

    protected function processBeforeSave($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);

        $this->eventManager->dispatch(
            'cataloginventory_stock_item_save_before',
            [
                'data_object' => $interceptor,
                'object' => $interceptor,
                'item' => $interceptor,
            ]
        );

        return $result;
    }

    //########################################

    public function aroundAfterSave($interceptor, \Closure $callback)
    {
        return $this->execute('afterSave', $interceptor, $callback);
    }

    // ---------------------------------------

    protected function processAfterSave($interceptor, \Closure $callback)
    {
        $result = $callback();

        $this->eventManager->dispatch(
            'cataloginventory_stock_item_save_after',
            [
                'data_object' => $interceptor,
                'object' => $interceptor,
                'item' => $interceptor,
            ]
        );

        return $result;
    }

    //########################################
}
