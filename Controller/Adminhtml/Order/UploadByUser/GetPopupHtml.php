<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order\UploadByUser;

class GetPopupHtml extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\Order\UploadByUser\Popup $block */
        $block = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\UploadByUser\Popup::class);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
