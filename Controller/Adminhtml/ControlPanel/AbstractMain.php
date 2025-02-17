<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel;

abstract class AbstractMain extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    public function _isAllowed(): bool
    {
        return true;
    }

    protected function _validateSecretKey(): bool
    {
        return true;
    }

    /**
     * It will allow to use control panel features even if extension is disabled, etc.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool
     */
    protected function preDispatch(\Magento\Framework\App\RequestInterface $request)
    {
        return true;
    }
}
