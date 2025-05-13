<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml;

abstract class AbstractMapping extends \M2E\TikTokShop\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::configuration_mapping');
    }
}
