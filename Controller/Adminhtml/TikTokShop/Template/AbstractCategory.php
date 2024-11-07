<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

abstract class AbstractCategory extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::configuration_categories');
    }
}
