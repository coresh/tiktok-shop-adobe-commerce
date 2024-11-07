<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log;

abstract class AbstractOrder extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::sales_logs');
    }
}
