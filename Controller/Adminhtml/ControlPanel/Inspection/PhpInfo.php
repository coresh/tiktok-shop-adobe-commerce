<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;
use M2E\TikTokShop\Helper\Module;
use Magento\Backend\App\Action;

/**
 * Class \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection\PhpInfo
 */
class PhpInfo extends AbstractMain
{
    public function execute()
    {
        phpinfo();
    }
}
