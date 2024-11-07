<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class PageActions extends AbstractBlock
{
    private const CONTROLLER_NAME = 'tikTokShop_order';

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml(): string
    {
        $shopSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Shop\Switcher::class)
            ->setData(['controller_name' => self::CONTROLLER_NAME]);

        $accountSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Account\Switcher::class)
            ->setData(['controller_name' => self::CONTROLLER_NAME]);

        $orderStateSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\NotCreatedFilter::class)
            ->setData(['controller' => self::CONTROLLER_NAME]);

        return
            '<div class="filter_block">'
            . $accountSwitcherBlock->toHtml()
            . $shopSwitcherBlock->toHtml()
            . $orderStateSwitcherBlock->toHtml()
            . '</div>'
            . parent::_toHtml();
    }
}
