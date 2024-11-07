<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    public function execute()
    {
        if ($this->isAjax()) {
            /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByListing\Grid $grid */
            $grid = $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByListing\Grid::class
            );
            $this->setAjaxContent($grid);

            return $this->getResult();
        }

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByListing $block */
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByListing::class
        );
        $this->addContent($block);

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Items By Listing'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-tiktok-shop-listings');

        return $this->getResult();
    }
}
