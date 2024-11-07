<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop;

abstract class AbstractOrder extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::sales_orders');
    }

    protected function init()
    {
        $this->addCss('order.css');
        $this->addCss('switcher.css');
        $this->addCss('tiktokshop/order/grid.css');

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Sales'));
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Orders'));
    }
}
