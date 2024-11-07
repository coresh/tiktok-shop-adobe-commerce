<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel;

use M2E\TikTokShop\Helper\Module;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

/**
 * Class \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\OverviewTab
 */
class OverviewTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Overview::class, '');
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
