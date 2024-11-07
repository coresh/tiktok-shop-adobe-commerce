<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Synchronization\Log;

class Grid extends \M2E\TikTokShop\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Synchronization\Log\Grid::class)
        );

        return $this->getResult();
    }
}
