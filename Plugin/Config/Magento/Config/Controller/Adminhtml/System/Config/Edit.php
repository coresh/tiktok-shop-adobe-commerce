<?php

namespace M2E\TikTokShop\Plugin\Config\Magento\Config\Controller\Adminhtml\System\Config;

/**
 * Class \M2E\TikTokShop\Plugin\Config\Magento\Config\Controller\Adminhtml\System\Config\Edit
 */
class Edit extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    //########################################

    protected function canExecute(): bool
    {
        if ($this->helperFactory->getObject('Module\Maintenance')->isEnabled()) {
            return false;
        }

        return true;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    // ---------------------------------------

    protected function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);

        if ($result instanceof \Magento\Backend\Model\View\Result\Redirect) {
            return $result;
        }

        $result->getConfig()->addPageAsset('M2E_TikTokShop::css/help_block.css');
        $result->getConfig()->addPageAsset('M2E_TikTokShop::css/system/config.css');

        return $result;
    }

    //########################################
}
