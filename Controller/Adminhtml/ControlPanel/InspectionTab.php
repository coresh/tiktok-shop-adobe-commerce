<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel;

use M2E\TikTokShop\Helper\Module;
use Magento\Backend\App\Action;

/**
 * Class \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\InspectionTab
 */
class InspectionTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Inspection::class,
            ''
        );
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
