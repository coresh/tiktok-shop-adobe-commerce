<?php

namespace M2E\TikTokShop\Block\Adminhtml;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class PageHelpLink extends AbstractBlock
{
    /** @var string */
    protected $_template = 'page_help_link.phtml';

    protected function _toHtml()
    {
        if ($this->getPageHelpLink() === null) {
            return '';
        }

        return parent::_toHtml();
    }
}
