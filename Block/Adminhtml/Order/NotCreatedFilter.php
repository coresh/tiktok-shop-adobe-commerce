<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class NotCreatedFilter extends AbstractContainer
{
    protected $_template = 'order/not_created_filter.phtml';

    //########################################

    public function getParamName()
    {
        return 'not_created_only';
    }

    public function getFilterUrl(): string
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = [];
        } else {
            $params = $this->getRequest()->getParams();
        }

        if ($this->isChecked()) {
            unset($params[$this->getParamName()]);
        } else {
            $params[$this->getParamName()] = true;
        }

        return $this->getUrl('*/' . $this->getData('controller') . '/*', $params);
    }

    public function isChecked()
    {
        return $this->getRequest()->getParam($this->getParamName());
    }

    //########################################
}
