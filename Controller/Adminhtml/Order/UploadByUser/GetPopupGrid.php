<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order\UploadByUser;

class GetPopupGrid extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\Order\UploadByUser\Grid $block */
        $block = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\UploadByUser\Grid::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
