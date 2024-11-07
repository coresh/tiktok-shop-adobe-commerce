<?php

namespace M2E\TikTokShop\Block\Adminhtml\Log;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Log\UniqueMessageFilter
 */
class UniqueMessageFilter extends AbstractContainer
{
    protected $_template = 'log/uniqueMessageFilter.phtml';

    //########################################

    public function getParamName()
    {
        return 'only_unique_messages';
    }

    public function getFilterUrl()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = [];
        } else {
            $params = $this->getRequest()->getParams();
        }

        if ($this->isChecked()) {
            $params[$this->getParamName()] = 0;
        } else {
            $params[$this->getParamName()] = 1;
        }

        return $this->getUrl($this->getData('route'), $params);
    }

    public function isChecked()
    {
        return $this->getRequest()->getParam($this->getParamName(), true);
    }

    //########################################
}
