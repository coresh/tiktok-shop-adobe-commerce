<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Log\Listing\Product;

use M2E\TikTokShop\Block\Adminhtml\Log\Listing\Product\AbstractView;

class View extends AbstractView
{
    protected function _toHtml()
    {
        $message = (string)__('This Log contains information about the actions applied to ' .
            'M2E TikTok Shop Connect Listings and related Items.');
        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)
            ->setData([
                'content' => $message,
            ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
