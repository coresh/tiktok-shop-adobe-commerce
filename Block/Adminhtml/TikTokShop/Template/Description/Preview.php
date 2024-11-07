<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Description;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class Preview extends AbstractBlock
{
    protected $_template = 'tiktokshop/template/description/preview.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->css->addFile('tiktokshop/template.css');
    }
}
