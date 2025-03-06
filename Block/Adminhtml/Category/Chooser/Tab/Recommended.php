<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Category\Chooser\Tab;

class Recommended extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('category/chooser/tab/recommended.phtml');
    }

    protected function _toHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/category/recommended'),
            '*/category/recommended',
        );

        return parent::_toHtml();
    }
}
