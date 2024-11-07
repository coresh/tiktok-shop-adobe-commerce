<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category;

class ViewFactory
{
    public function create(
        \Magento\Framework\View\LayoutInterface $layout,
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary
    ): View {
        /** @var View $block */
        $block = $layout->createBlock(
            View::class,
            '',
            ['dictionary' => $dictionary]
        );

        return $block;
    }
}
