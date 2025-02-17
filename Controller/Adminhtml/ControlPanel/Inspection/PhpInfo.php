<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;

class PhpInfo extends AbstractMain
{
    public function execute()
    {
        phpinfo();
    }
}
