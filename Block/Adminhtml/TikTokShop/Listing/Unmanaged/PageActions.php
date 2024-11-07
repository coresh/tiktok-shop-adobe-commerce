<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class PageActions extends AbstractBlock
{
    public const BLOCK_PATH = 'TikTokShop_Listing_Unmanaged_PageActions';
    private const CONTROLLER_NAME = 'tikTokShop_listing_unmanaged/index';

    /**
     * @ingeritdoc
     */
    protected function _toHtml()
    {
        $accountSwitcherBlock = $this->createSwitcher(
            \M2E\TikTokShop\Block\Adminhtml\Account\Switcher::class
        );

        $shopSwitcherBlock = $this->createSwitcher(
            \M2E\TikTokShop\Block\Adminhtml\Shop\Switcher::class
        );

        return
            '<div class="filter_block">'
            . $accountSwitcherBlock->toHtml()
            . $shopSwitcherBlock->toHtml()
            . '</div>'
            . parent::_toHtml();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createSwitcher(string $blockClassName): \M2E\TikTokShop\Block\Adminhtml\Switcher
    {
        return $this->getLayout()
                    ->createBlock($blockClassName)
                    ->setData([
                        'controller_name' => self::CONTROLLER_NAME,
                    ]);
    }
}
