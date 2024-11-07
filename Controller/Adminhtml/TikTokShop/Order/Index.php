<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder;

class Index extends AbstractOrder
{
    public function execute()
    {
        $this->init();
        $this->addContent($this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order::class));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-tiktok-shop-orders');

        return $this->getResultPage();
    }
}
