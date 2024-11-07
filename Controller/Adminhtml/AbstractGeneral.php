<?php

namespace M2E\TikTokShop\Controller\Adminhtml;

abstract class AbstractGeneral extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::tts');
    }
}
